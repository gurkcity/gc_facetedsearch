<?php

/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */

namespace Onlineshopmodule\PrestaShop\Module\Facetedsearch\Module;

use PrestaShop\Autoload\PrestashopAutoload;

class OverrideTools
{
    public function __construct(
        protected \GC_Facetedsearch $module
    ) {

    }

    public function removeOverride(
        string $className,
        string|array $methodNames = [],
        string|array $propertyNames = [],
        string|array $constantNames = []
    ): bool
    {
        if (is_string($methodNames)) {
            $methodNames = [$methodNames];
        }

        if (is_string($propertyNames)) {
            $propertyNames = [$propertyNames];
        }

        if (is_string($constantNames)) {
            $constantNames = [$constantNames];
        }

        $coreClassName = $className . 'Core';

        // Check for core class
        $coreFile = PrestashopAutoload::getInstance()->getClassPath($coreClassName);
        $overrideFile = PrestashopAutoload::getInstance()->getClassPath($className);

        if ($coreFile && !$overrideFile) {
            // Core class exists, but no override file exists, so nothing to remove
            return true;
        }

        $override_path = _PS_OVERRIDE_DIR_ . $overrideFile;

        // Check for module core class
        if (!$coreFile && \Module::getModuleIdByName(strtolower($className))) {
            $coreFile = 'modules' . DIRECTORY_SEPARATOR . strtolower($className) . DIRECTORY_SEPARATOR . strtolower($className) . '.php';
            $override_path = _PS_ROOT_DIR_ . '/override/' . $coreFile;
            $className = $className . 'Override';
            $coreClassName = $className;
        }

        if (!is_file($override_path)) {
            return true;
        }

        if (!is_writable($override_path)) {
            return false;
        }

        file_put_contents($override_path, preg_replace('#(\r\n|\r)#ism', "\n", file_get_contents($override_path)));

        if (!$coreFile) {
            return true;
        }

        $code = '';

        // Get a uniq id for the class, because you can override a class (or remove the override) twice in the same session and we need to avoid redeclaration
        do {
            $uniq = uniqid();
            $reflectionClassName = $className . 'OverrideOriginal_remove' . $uniq;
        } while (class_exists($reflectionClassName, false));

        // Make a reflection of the override class and the module override class
        $overrideContent = file($override_path);

        eval(
            preg_replace(
                [
                    '#^\s*<\?(?:php)?#',
                    '#class\s+' . $className . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i',
                ],
                [
                    ' ',
                    'class ' . $reflectionClassName . ' extends \stdClass',
                ],
                implode('', $overrideContent)
            )
        );

        $override_class = new \ReflectionClass($reflectionClassName);

        // Remove method from override file
        foreach ($methodNames as $methodName) {
            if (!$override_class->hasMethod($methodName)) {
                continue;
            }

            $method = $override_class->getMethod($methodName);
            $length = $method->getEndLine() - $method->getStartLine() + 1;

            preg_replace('/\s/', '', implode('', array_splice($overrideContent, $method->getStartLine() - 1, $length, array_pad([], $length, '#--remove--#'))));

            if (
                isset($overrideContent[$method->getStartLine() - 5])
                &&preg_match('/\* module: (' . $this->module->name . ')/ism', $overrideContent[$method->getStartLine() - 5])
            ) {
                $overrideContent[$method->getStartLine() - 6] = $overrideContent[$method->getStartLine() - 5] = $overrideContent[$method->getStartLine() - 4] = $overrideContent[$method->getStartLine() - 3] = $overrideContent[$method->getStartLine() - 2] = '#--remove--#';
            }
        }

        // Remove properties from override file
        foreach ($propertyNames as $propertyName) {
            if (!$override_class->hasProperty($propertyName)) {
                continue;
            }

            // Replace all declaration lines by #--remove--#, tracking bracket depth
            // to handle multi-line property values (e.g. arrays spanning multiple lines)
            $inside_property = false;
            $bracket_depth = 0;

            foreach ($overrideContent as $line_number => &$line_content) {
                if (!$inside_property) {
                    if (preg_match('/(public|private|protected)\s+(static\s+)?\s*(\w+\s+)?(\$)?' . $propertyName . '/i', $line_content)) {
                        if (
                            isset($overrideContent[$line_number - 4])
                            && preg_match('/\* module: (' . $this->module->name . ')/ism', $overrideContent[$line_number - 4])
                        ) {
                            $overrideContent[$line_number - 5] = $overrideContent[$line_number - 4] = $overrideContent[$line_number - 3] = $overrideContent[$line_number - 2] = $overrideContent[$line_number - 1] = '#--remove--#';
                        }
                        $inside_property = true;
                        $bracket_depth = 0;
                    }
                }

                if ($inside_property) {
                    $bracket_depth += substr_count($line_content, '(') - substr_count($line_content, ')');
                    $bracket_depth += substr_count($line_content, '[') - substr_count($line_content, ']');
                    $is_end = ($bracket_depth <= 0 && false !== strpos($line_content, ';'));
                    $line_content = '#--remove--#';

                    if ($is_end) {
                        break;
                    }
                }
            }
        }

        // Remove constants from override file
        foreach ($constantNames as $constantName) {
            if (!$override_class->hasConstant($constantName)) {
                continue;
            }

            // Replace all declaration lines by #--remove--#, tracking bracket depth
            // to handle multi-line constant values (e.g. arrays spanning multiple lines)
            $inside_constant = false;
            $bracket_depth = 0;
            foreach ($overrideContent as $line_number => &$line_content) {
                if (!$inside_constant) {
                    if (preg_match('/(const)\s+(static\s+)?(\$)?' . $constantName . '/i', $line_content)) {
                        if (
                            isset($overrideContent[$line_number - 4])
                            && preg_match('/\* module: (' . $this->module->name . ')/ism', $overrideContent[$line_number - 4])
                        ) {
                            $overrideContent[$line_number - 5] = $overrideContent[$line_number - 4] = $overrideContent[$line_number - 3] = $overrideContent[$line_number - 2] = $overrideContent[$line_number - 1] = '#--remove--#';
                        }
                        $inside_constant = true;
                        $bracket_depth = 0;
                    }
                }

                if ($inside_constant) {
                    $bracket_depth += substr_count($line_content, '(') - substr_count($line_content, ')');
                    $bracket_depth += substr_count($line_content, '[') - substr_count($line_content, ']');
                    $is_end = ($bracket_depth <= 0 && false !== strpos($line_content, ';'));
                    $line_content = '#--remove--#';

                    if ($is_end) {
                        break;
                    }
                }
            }
        }

        $count = count($overrideContent);

        for ($i = 0; $i < $count; ++$i) {
            if (preg_match('/(^\s*\/\/.*)/i', $overrideContent[$i])) {
                $overrideContent[$i] = '#--remove--#';
            } elseif (preg_match('/(^\s*\/\*)/i', $overrideContent[$i])) {
                if (!preg_match('/(^\s*\* module:)/i', $overrideContent[$i + 1])
                    && !preg_match('/(^\s*\* date:)/i', $overrideContent[$i + 2])
                    && !preg_match('/(^\s*\* version:)/i', $overrideContent[$i + 3])
                    && !preg_match('/(^\s*\*\/)/i', $overrideContent[$i + 4])) {
                    for (; $overrideContent[$i] && !preg_match('/(.*?\*\/)/i', $overrideContent[$i]); ++$i) {
                        $overrideContent[$i] = '#--remove--#';
                    }
                    $overrideContent[$i] = '#--remove--#';
                }
            }
        }

        // Rewrite nice code
        foreach ($overrideContent as $line) {
            if ($line == '#--remove--#') {
                continue;
            }

            $code .= $line;
        }

        $to_delete = preg_match(
            '/<\?(?:php)?\s+(?:abstract|interface)?\s*?class\s+' . $className . '\s+extends\s+' . $coreClassName . '\s*?[{]\s*?[}]/ism',
            $code
        );

        if (!$to_delete) {
            do {
                $uniq = uniqid();
                $reflectionClassName = $className . 'OverrideOriginal_check' . $uniq;
            } while (class_exists($reflectionClassName, false));

            // To detect if the class has remaining code, we dynamically create a class which contains the remaining code.
            eval(
                preg_replace(
                    [
                        '#^\s*<\?(?:php)?#',
                        '#class\s+' . $className . '\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i',
                    ],
                    [
                        ' ',
                        'class ' . $reflectionClassName . ' extends \stdClass',
                    ],
                    $code
                )
            );

            // Then we use ReflectionClass to analyze what this code actually contains
            $override_class = new \ReflectionClass($reflectionClassName);

            // If no valuable code remains then we can delete it
            $to_delete = $override_class->getConstants() === []
                && $override_class->getProperties() === []
                && $override_class->getMethods() === [];
        }

        if (!isset($to_delete) || $to_delete) {
            // Remove file
            unlink($override_path);
        } else {
            file_put_contents($override_path, $code);
        }

        // Re-generate the class index
        PrestashopAutoload::getInstance()->generateIndex();

        return true;
    }

    public function deleteModuleOverrides(): bool
    {
        $overridePath = $this->module->getLocalPath() . 'override/';

        return \Tools::deleteDirectory($overridePath);
    }
}
