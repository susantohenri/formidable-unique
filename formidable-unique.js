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
                var values = {}
                for (var field of sources) values[field] = jQuery(`[name="item_meta[${field}]"]`).val()
                jQuery.post(formidable_unique.generator_url, {
                    target: target.attr(`name`).replace(`item_meta[`,``).replace(`]`, ``),
                    combination,
                    sources,
                    values
                }, unique => {
                    target.val(unique)
                })
            })
        }
    })
})