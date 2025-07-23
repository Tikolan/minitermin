(function (blocks, element, blockEditor, components) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;

    blocks.registerBlockType('minitermin/block', {
        title: 'MiniTermin',
        icon: 'admin-post',
        category: 'widgets',
        attributes: {
            datumVon: { type: 'string', default: '' },
            datumBis: { type: 'string', default: '' },
            zeitVon: { type: 'string', default: '' },
            zeitBis: { type: 'string', default: '' },
            ort: { type: 'string', default: '' },
            showExpiredText: { type: 'boolean', default: false },
            expiredText: { type: 'string', default: 'Termin abgelaufen' }
        },
        edit: function (props) {
            var attrs = props.attributes;
            var setAttrs = props.setAttributes;

            // Helper function to check if the event is expired
            function isExpired() {
                var today = new Date();
                var datumBis = attrs.datumBis ? new Date(attrs.datumBis) : new Date(attrs.datumVon);
                var zeitBis = attrs.zeitBis ? attrs.zeitBis : attrs.zeitVon;

                if (!datumBis) return false;

                // If time is set, combine date and time
                if (zeitBis) {
                    var parts = zeitBis.split(':');
                    datumBis.setHours(parseInt(parts[0], 10), parseInt(parts[1], 10) || 0, 0, 0);
                } else {
                    datumBis.setHours(23, 59, 59, 999); // End of day
                }

                return today > datumBis;
            }

            var expired = isExpired();

            return el(
                element.Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Termindaten', initialOpen: true },
                        el(TextControl, {
                            label: 'Datum von',
                            type: 'date',
                            value: attrs.datumVon,
                            onChange: function (val) { setAttrs({ datumVon: val }); }
                        }),
                        el(TextControl, {
                            label: 'Datum bis',
                            type: 'date',
                            value: attrs.datumBis,
                            onChange: function (val) { setAttrs({ datumBis: val }); }
                        }),
                        el(TextControl, {
                            label: 'Zeit von',
                            type: 'time',
                            value: attrs.zeitVon,
                            onChange: function (val) { setAttrs({ zeitVon: val }); }
                        }),
                        el(TextControl, {
                            label: 'Zeit bis',
                            type: 'time',
                            value: attrs.zeitBis,
                            onChange: function (val) { setAttrs({ zeitBis: val }); }
                        }),
                        el(TextControl, {
                            label: 'Ort',
                            value: attrs.ort,
                            onChange: function (val) { setAttrs({ ort: val }); }
                        }),
                        el(components.ToggleControl, {
                            label: 'Abgelaufenen Termin anzeigen',
                            checked: attrs.showExpiredText,
                            onChange: function (val) { setAttrs({ showExpiredText: val }); }
                        }),
                        attrs.showExpiredText && el(TextControl, {
                            label: 'Text für abgelaufenen Termin',
                            value: attrs.expiredText,
                            onChange: function (val) { setAttrs({ expiredText: val }); }
                        }),
                        

                    )
                ),
               el(
    'div',
    { className: 'MiniTermin' },
    el('strong', {}, 'Termin:'),
    attrs.datumVon && el('div', {}, 'Von: ' + attrs.datumVon),
    attrs.datumBis && el('div', {}, 'Bis: ' + attrs.datumBis),
    attrs.zeitVon && el('div', {}, 'Zeit von: ' + attrs.zeitVon),
    attrs.zeitBis && el('div', {}, 'Zeit bis: ' + attrs.zeitBis),
    attrs.ort && el('div', {}, 'Ort: ' + attrs.ort),
    expired && attrs.showExpiredText && el('div', {}, 'Abgelaufener Termin: ' + attrs.expiredText)
)
            ); 
        },
        save: function () {
            // Ausgabe erfolgt dynamisch per PHP (render_callback)
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
);