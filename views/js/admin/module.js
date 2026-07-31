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
  const layeredDefaultCategory = $('input[name="configuration[FILTER_BY_DEFAULT_CATEGORY]"]');
  layeredDefaultCategory.on('change', function initializeOptions(event) {
    let elm = $(this);

    if (!elm.prop('checked')) {
      return;
    }

    if (elm.val() === '1') {
      $('input[name="configuration[FULL_TREE]"][value="0"]').prop('checked', true);
      $('input[name="configuration[FULL_TREE]"]').prop('disabled', true);
    } else {
      $('input[name="configuration[FULL_TREE]"]').prop('disabled', false);
    }
  });

  layeredDefaultCategory.filter('[value="1"]').trigger('change');
});
/**
 * Scripts of Faceted search module
 */
$(document).ready(() => {
  $('.ajaxcall').click(function onAjaxCall() {
    if (this.legend === undefined) {
      this.legend = $(this).html();
    }

    if (this.running === undefined) {
      this.running = false;
    }

    if (this.running === true) {
      return false;
    }

    $('.ajax-message').hide();
    this.running = true;

    if (typeof (this.restartAllowed) === 'undefined' || this.restartAllowed) {
      $(this).html(this.legend + translations.in_progress);
      $('#indexing-warning').show();
    }

    this.restartAllowed = false;
    const type = $(this).attr('rel');

    $.ajax({
      url: `${this.href}&ajax=1`,
      context: this,
      dataType: 'json',
      cache: 'false',
      success() {
        this.running = false;
        this.restartAllowed = true;
        $('#indexing-warning').hide();
        $(this).html(this.legend);

        $('#ajax-message-ok span').html(
          type === 'price' ? translations.url_indexation_finished : translations.attribute_indexation_finished,
        );

        $('#ajax-message-ok').show();
      },
      error() {
        this.restartAllowed = true;
        $('#indexing-warning').hide();

        $('#ajax-message-ko span').html(
          type === 'price' ? translations.url_indexation_failed : translations.attribute_indexation_failed,
        );

        $('#ajax-message-ko').show();
        $(this).html(this.legend);
        this.running = false;
      },
    });

    return false;
  });

  let totalCount = 0;
  $('.ajaxcall-recurcive').each((it, elm) => {
    $(elm).click(function onAjaxRecursiveCall(e) {
      e.preventDefault();

      if (this.cursor === undefined) {
        this.cursor = 0;
      }

      if (this.legend === undefined) {
        this.legend = $(this).html();
      }

      if (this.running === undefined) {
        this.running = false;
      }

      if (this.running === true) {
        return false;
      }

      $('.ajax-message').hide();

      this.running = true;

      if (typeof (this.restartAllowed) === 'undefined' || this.restartAllowed) {
        $(this).html(this.legend + translations.in_progress);
        $('#indexing-warning').show();
      }

      this.restartAllowed = false;

      $.ajax({
        url: `${this.href}&ajax=1&cursor=${this.cursor}`,
        context: this,
        dataType: 'json',
        cache: 'false',
        success(res) {
          this.running = false;
          if (res.result) {
            this.cursor = 0;
            totalCount = 0;
            $('#indexing-warning').hide();
            $(this).html(this.legend);
            $('#ajax-message-ok span').html(translations.price_indexation_finished);
            $('#ajax-message-ok').show();
            return;
          }

          totalCount += parseInt(res.count, 10);
          this.cursor = parseInt(res.cursor, 10);
          $(this).html(
            this.legend + translations.price_indexation_in_progress.replace(
              '%s',
              `${totalCount}/${res.total}`,
            ),
          );
          $(this).click();
        },
        error(res) {
          this.restartAllowed = true;
          $('#indexing-warning').hide();
          $('#ajax-message-ko span').html(translations.price_indexation_failed);
          $('#ajax-message-ko').show();
          $(this).html(this.legend);

          this.cursor = 0;
          this.running = false;
        },
      });
      return false;
    });
  });

  if (typeof Sortable !== 'undefined') {
    const listFilters = document.getElementById('list-filters');

    if (listFilters !== null) {
      new Sortable(listFilters, {
        animation: 150,
        ghostClass: 'sortable-ghost',
      });
    }
  } else {
    $('.sortable').sortable({
      forcePlaceholderSize: true,
    });
  }

  function enableFilter() {
    $('#selected_filters').html($('.filter_list_item .filter-switch[value="1"]:checked').length);
  }
  $('.filter_list_item .filter-switch').on('change', enableFilter);
  enableFilter();
});
