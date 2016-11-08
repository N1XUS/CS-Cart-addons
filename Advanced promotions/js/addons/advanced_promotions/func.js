(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        moment.locale(Tygh.cart_language);
        var elms = $(".cm-moment-format");
        if (elms.length > 0) {
            elms.each(function() {
                var format = $(this).attr('data-format') || "D MMMM";
                var date = moment.unix($(this).attr('data-timestamp')).format(format);
                $(this).text(date);
            });
        }
        var elms = $(".cm-moment-calendar-format");
        if (elms.length > 0) {
            elms.each(function() {
                var text = moment.unix($(this).attr('data-timestamp')).fromNow();
                $(this).text(text);
            });
        }
        var elms = $(".cm-clipclock");
        if (elms.length > 0) {
            elms.each(function() {
                $(this).FlipClock($(this).attr('data-timestamp'), {
                    clockFace: 'DailyCounter',
                    countdown: true,
                    language: Tygh.cart_language
                });
            });
        }
    });
})(Tygh, Tygh.$);

(function($) {

  /**
   * FlipClock Ukrainian Language Pack
   *
   * This class will used to translate tokens into the Ukrainian language.
   *
   */

  FlipClock.Lang.Ukrainian = {

    'years'   : 'років',
    'months'  : 'місяців',
    'days'    : 'днів',
    'hours'   : 'годин',
    'minutes' : 'хвилин',
    'seconds' : 'секунд'

  };

  /* Create various aliases for convenience */

  FlipClock.Lang['uk']      = FlipClock.Lang.Ukrainian;
  FlipClock.Lang['uk-ua']   = FlipClock.Lang.Ukrainian;
  FlipClock.Lang['ukrainian']  = FlipClock.Lang.Ukrainian;

}(jQuery));