jQuery(function () {
    jQuery(`[${formidable_unique.attribute}]`).each(function () {
        var target = jQuery(this)
        var combination = jQuery(this).attr(formidable_unique.attribute)
        var sources = []
        for (var phrase of combination.split(formidable_unique.separator)) {
            if (0 === phrase.indexOf(formidable_unique.field_identifier)) {
                sources.push(phrase.replace(formidable_unique.field_identifier, ``))
            }
        }
        for (var source of sources) {
            jQuery(`[name="item_meta[${source}]"]`).change(function () {
                jQuery.post(formidable_unique.generator_url, {

                }, unique => {
                    target.val(unique)
                })
            })
        }
    })
})