/**
 * GC Facetedsearch
 * Module for PrestaShop E-Commerce Software
 *
 * @author    Markus Engel <info@onlineshop-module.de>
 * @copyright Copyright (c) 2026, Onlineshop-Module.de
 * @license   commercial, see licence.txt
 */
const gc_facetedsearch = {
  init: () => {
    const info_panel = $('.info_panel');
    const cronjob_container = $('.cronjobs .job');
    const btn_change_license_code = $('#btn_change_license_code');
    const help_link = $('.toolbar-icons .btn-help');

    info_panel.find('.panel-footer .expand_info').on('click', (e) => {
      e.preventDefault();

      const element = $(e.currentTarget);

      if (!info_panel.hasClass('closed')) {
        info_panel.addClass('closed');

        element.find('i').text('expand_more');

        localStorage.setItem('toggle_gc_header', 'closed');
      } else {
        info_panel.removeClass('closed');

        element.find('i').text('expand_less');

        localStorage.setItem('toggle_gc_header', 'opened');
      }
    });

    const toggle_gc_header = localStorage.getItem('toggle_gc_header');

    if (toggle_gc_header == 'closed') {
      info_panel.addClass('closed');

      info_panel.find('.panel-footer .expand_info i').text('expand_more');
    }

    cronjob_container.find('.job_open').on('click', (e) => {
      e.preventDefault();

      const element = $(e.currentTarget);

      element.hide().closest('.job').find('.job_body').show();

      $('.job_close').show();
    });

    cronjob_container.find('.job_close').on('click', (e) => {
      e.preventDefault();

      const element = $(e.currentTarget);

      element.hide().closest('.job_body').hide();

      $('.job_open').show();
    });

    btn_change_license_code.on('click', (e) => {
      if (window.confirm(txtUpdateLicenseCode)) {
        return true;
      } else {
        e.preventDefault();

        return false;
      }
    });

    $('input[name="payment[awaiting_payment]"]').on('change', (e) => {
      gc_facetedsearch.togglePaymentRow();
    });

    $('input[name="payment[show_payment_logo]"]').on('change', (e) => {
      gc_facetedsearch.toggleLogoRow();
    });

    gc_facetedsearch.togglePaymentRow();
    gc_facetedsearch.toggleLogoRow();

    gc_facetedsearch.rearrangeTabs();

    $(help_link).attr('target', '_blank');
  },

  togglePaymentRow: () => {
    gc_facetedsearch.toggleRow('input[name="payment[awaiting_payment]"]', '.awaiting_payment_os_row');
  },

  toggleLogoRow: () => {
    gc_facetedsearch.toggleRow('input[name="payment[show_payment_logo]"]', '.payment_logo_row');
  },

  toggleRow: (radioSelector, rowSelector) => {
    if ($(radioSelector).length === 0) {
      return;
    }

    let currentValue = $(radioSelector + ':checked').val();

    if (currentValue == value) {
      $(rowSelector).show();
    } else {
      $(rowSelector).hide();
    }
  },

  rearrangeTabs: () => {
    if (
      typeof tabClassnames === 'undefined'
      || tabClassnames.length === 0
    ) {
      return;
    }

    for (classname of tabClassnames) {
      let id = 'subtab-' + classname;
      let menuItem = $('#head_tabs #' + id).parent();

      if (menuItem.length === 0) {
        continue;
      }

      $('#head_tabs .nav:first-child').append(menuItem);
    };
  }
}

$(() => {
  $('#info_panel').before($('#header-multishop'));
  $('#info_panel').after($('#head_tabs'));
  $('#head_tabs').append($('.info_buttons'));

  for (const key in menuIcons) {
    let menuItem = $('#head_tabs .nav a#subtab-' + key);

    if (menuItem.length === 0) {
      continue;
    }

    let icon = menuIcons[key];

    menuItem.prepend('<i class="material-icons">' + icon + '</i> ');
  }

  gc_facetedsearch.init();

  /**
   * Custom javascript here:
   */

});
