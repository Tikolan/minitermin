(function (blocks, element, components, blockEditor, data) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var CheckboxControl = components.CheckboxControl;
        var useSelect = data.useSelect;


    // Kategorien und CPTs dynamisch laden (hier als Beispiel statisch)
    var postTypes = [
        { label: 'Beiträge', value: 'post' },
        { label: 'Seiten', value: 'page' },
        // Weitere CPTs hier ergänzen
    ];
   /* var categories = [
        { label: 'Alle Kategorien', value: '' },
        { label: 'Kategorie 1', value: 1 },
        { label: 'Kategorie 2', value: 2 },
        // Dynamisch laden via REST API möglich
    ];
*/
    blocks.registerBlockType('minitermin/liste', {
        title: 'MiniTermin Liste',
        icon: 'list-view',
        category: 'widgets',
        attributes: {
            categories: { type: 'array', default: [] },
            postType: { type: 'string', default: 'post' },
            sortOrder: { type: 'string', default: 'asc' },
            showType: { type: 'string', default: 'all' },
        },
        edit: function (props) {
            var attrs = props.attributes;
            var setAttrs = props.setAttributes;
            var categories = useSelect(function(select) {
                    return select('core').getEntityRecords('taxonomy', 'category', { per_page: -1 });
                }, []);

                var categoryOptions = [{ label: 'Alle Kategorien', value: '' }];
                if (categories) {
                    categories.forEach(function(cat) {
                        categoryOptions.push({ label: cat.name, value: cat.id });
                    });
                }

            return el(
                element.Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Filter', initialOpen: true },
                        el(SelectControl, {
                            label: 'Beitrags-Typ',
                            value: attrs.postType,
                            options: postTypes,
                            onChange: function (val) { setAttrs({ postType: val }); }
                        }),
                       
                        el(SelectControl, {
                            label: 'Kategorien',
                            multiple: true,
                            value: attrs.categories,
                            options: categoryOptions,
                            onChange: function (val) {
                           // Wenn "Alle Kategorien" ausgewählt, dann leeres Array
        if (Array.isArray(val) && (val.includes('') || val.length === 0)) {
            setAttrs({ categories: [] });
        } else {
            setAttrs({ categories: Array.isArray(val) ? val : [val] });
        }
                            }
                        }),
                        el(SelectControl, {
                            label: 'Sortierung',
                            value: attrs.sortOrder,
                            options: [
                                { label: 'Vergangenheit bis Zukunft', value: 'asc' },
                                { label: 'Zukuft bis Vergangenheit', value: 'desc' },

                            ],
                            onChange: function (val) { setAttrs({ sortOrder: val }); }
                        }),
                        el(SelectControl, {
                            label: 'Anzeigen',
                            value: attrs.showType,
                            options: [
                                { label: 'Alle Termine', value: 'all' },
                                { label: 'Nur kommende Termine', value: 'upcoming' },
                                { label: 'Nur vergangene Termine', value: 'past' },
                            ],
                            onChange: function (val) { setAttrs({ showType: val }); }
                        })
                    )
                ),
                el('div', {}, 'Die gefilterte Terminliste wird im Frontend angezeigt.',
                 el('div', { style: { marginTop: '1em' } }, 'Im Frontend erscheint ein iCal-Link für diese Liste.')
)
            );
        },
        save: function () {
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor,
    window.wp.data,
);