$(document).ready(function() {

  // focus the username field on login page
  $('#username').focus();

  // clock widget
  let date = formatDate(new Date());
  $('#dashboard-widget-clock-date').html(date);
  setInterval(function() {

    let seconds = new Date().getSeconds();
    $('#dashboard-widget-clock-seconds').html((seconds < 10 ? '0' : '') + seconds);

    let minutes = new Date().getMinutes();
    $('#dashboard-widget-clock-minutes').html((minutes < 10 ? '0' : '') + minutes);

    let hours = new Date().getHours();
    $('#dashboard-widget-clock-hours').html((hours < 10 ? '0' : '') + hours);

  }, 1000);

  // dropdown menu
  $('#aside_menu a[href="#"]').on('click', function() {
    $('#sidebar').removeClass('aside-collapsed');

    if($(this).parent().find('ul').hasClass('dropdown-active')) {
      $(this).parent().find('ul').removeClass('dropdown-active');
      $(this).find('span.dropdown').removeClass('dropdown-open');
    } else {
      $('#aside_menu ul ul').removeClass('dropdown-active');
      $('#aside_menu').find('span.dropdown').removeClass('dropdown-open');
      $(this).parent().find('ul').addClass('dropdown-active');
      $(this).find('span.dropdown').addClass('dropdown-open');
    }

    return false;
  });

  // profile dropdown menu
  $('#profile_avatar').on('click', function() {
    $('#profile_dropdown').toggle();
    return false;
  });

  $(document).on('click', function() {
    $("#profile_dropdown:not(:hover)").hide();
  });

  // permissions - check on parent checked
  $('.module_checkbox').on('change', function() {
    let checked = $(this).is(':checked');
    $(this).parent().parent().find('ul').find('input[type="checkbox"]').prop('checked', checked);
  });

  // permissions - check parent if all children checked
  $('.permission_checkbox').on('change', function() {
    let allCheckboxes = $(this).closest('ul').find('input[type="checkbox"]').length;
    let checkedCheckboxes = $(this).closest('ul').find('input[type="checkbox"]:checked').length;
    let moduleCheckbox = $(this).closest('div').find('.module_checkbox');

    if(allCheckboxes == checkedCheckboxes) {
      moduleCheckbox.prop('checked', true);
    } else {
      moduleCheckbox.prop('checked', false);
    }
  });

  // textarea character counter
  $("textarea#message").on('keyup paste', function() {
    let count = $(this).val().replace(/(<([^>]+)>)/ig,"").length;
    if($(this).data('limit')) {
      let remaining = $(this).data('limit') - count;
      let text = classname = '';
      if (remaining < 0) {
        text = (remaining * -1) + ' Characters Over Limit';
        classname = 'charcounter-over-limit'
      } else {
        text = remaining + " Characters left";
        classname = 'charcounter-within-limit';
      }
      $("#charcounter").text(text).removeClass().addClass(classname);
    } else {
      $("#charcounter").text(count + " Characters");
    }
  });

  // window width
  let window_width = $(window).width();

  // change table display to inline-block if the width is more than the container
  tableDisplayInlineBlock();
  $(window).on('resize', function() {
    new_window_width = $(this).width();
    if(new_window_width !== window_width) {
      tableDisplayInlineBlock();
      window_width = new_window_width;
    }
  });

  function tableDisplayInlineBlock() {
    let container_width = $('.main-section').width();
    $('table').each(function() {
      if($(this).find('thead').width() > container_width) {
        $(this).css("display", "inline-block");
      } else {
        $(this).css("display", "table");
      }
    });
  }

  function nth(d) {
    if(d > 3 && d < 21) {
      return 'th';
    }
    switch (d % 10) {
      case 1: return 'st'; break;
      case 2: return 'nd'; break;
      case 3: return 'rd'; break;
      default: return 'th';
    }
  }

  function formatDate(date) {
    let monthNames = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December'
    ];
    let dayNames = [
      'Sunday',
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday'
    ];

    // returns format: <span>Sunday</span><span class="comma-sep">, </span><span>1st January 1980</span>
    return '<span>' + dayNames[date.getDay()] +'</span>' +
      '<span class="comma-sep">,&nbsp;</span>' +
      '<span>' +
      date.getDate() + nth(date.getDate()) + " " +
      monthNames[date.getMonth()] + " " +
      date.getFullYear() +
      '</span>';
  }

});
