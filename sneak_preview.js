(function ($) {

/**
 * Custom summary for the Sneak Preview module vertical tab.
 */
Drupal.behaviors.sneak_previewFieldsetSummaries = {
  attach: function (context) {
    $('fieldset#edit-sneak-preview', context).drupalSetSummary(function (context) {
      if ($('#edit-sneak-preview-provide', context).attr('checked')) {
        return Drupal.t('Link provided');
      }
      else {
        return Drupal.t('No link');
      }
    });
  }
};

})(jQuery);