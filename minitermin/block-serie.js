(function (blocks, element, components, blockEditor) {
    var el = element.createElement;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;
    var SelectControl = components.SelectControl;
    var DateTimePicker = components.__experimentalDateTimePicker || components.DateTimePicker;

    blocks.registerBlockType('minitermin/serie', {
        title: 'MiniTermin Serie',
        icon: 'calendar-alt',
        category: 'widgets',
        attributes: {
            startDate: { type: 'string', default: '' },
            endDate: { type: 'string', default: '' },
            timeFrom: { type: 'string', default: '' },
            timeTo: { type: 'string', default: '' },
            repeatType: { type: 'string', default: 'daily' },
            repeatEvery: { type: 'number', default: 1 },
            weekday: { type: 'string', default: 'MO' },
            monthday: { type: 'number', default: 1 },
            location: { type: 'string', default: '' },
            monthlyWeek: { type: 'string', default: '1' }, // 1,2,3,4,5,last
            monthlyWeekday: { type: 'string', default: 'MO' }, // MO, TU, ...
            showExpiredText: { type: 'boolean', default: false },
            expiredText: { type: 'string', default: 'Termin abgelaufen' }
        },
        edit: function (props) {
            var attrs = props.attributes;
            var setAttrs = props.setAttributes;

            return el(
                element.Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        PanelBody,
                        { title: 'Serientermin', initialOpen: true },
                        el(TextControl, {
                            label: 'Startdatum',
                            type: 'date',
                            value: attrs.startDate,
                            onChange: function (val) { setAttrs({ startDate: val }); }
                        }),
                        el(TextControl, {
                            label: 'Enddatum',
                            type: 'date',
                            value: attrs.endDate,
                            onChange: function (val) { setAttrs({ endDate: val }); }
                        }),
                        el(TextControl, {
                            label: 'Uhrzeit von',
                            type: 'time',
                            value: attrs.timeFrom,
                            onChange: function (val) { setAttrs({ timeFrom: val }); }
                        }),
                        el(TextControl, {
                            label: 'Uhrzeit bis',
                            type: 'time',
                            value: attrs.timeTo,
                            onChange: function (val) { setAttrs({ timeTo: val }); }
                        }),
                        el(SelectControl, {
                            label: 'Wiederholung',
                            value: attrs.repeatType,
                            options: [
                                { label: 'Täglich', value: 'daily' },
                                { label: 'Wöchentlich', value: 'weekly' },
                                { label: 'Monatlich', value: 'monthly' },
                                { label: 'Jährlich', value: 'yearly' },
                            ],
                            onChange: function (val) { setAttrs({ repeatType: val }); }
                        }),
                        el(TextControl, {
                            label: 'Alle wieviel Tage/Wochen/Monate/Jahre?',
                            type: 'number',
                            value: attrs.repeatEvery,
                            onChange: function (val) { setAttrs({ repeatEvery: parseInt(val) || 1 }); }
                        }),
                        attrs.repeatType === 'weekly' && el(SelectControl, {
                            label: 'Wochentag',
                            value: attrs.weekday,
                            options: [
                                { label: 'Montag', value: 'MO' },
                                { label: 'Dienstag', value: 'DI' },
                                { label: 'Mittwoch', value: 'MI' },
                                { label: 'Donnerstag', value: 'DO' },
                                { label: 'Freitag', value: 'FR' },
                                { label: 'Samstag', value: 'SA' },
                                { label: 'Sonntag', value: 'SO' },
                            ],
                            onChange: function (val) { setAttrs({ weekday: val }); }
                        }),
                        attrs.repeatType === 'monthly' && el(SelectControl, {
                            label: 'Jede/n',
                            value: attrs.monthlyWeek,
                            options: [
                                { label: 'Erste', value: '1' },
                                { label: 'Zweite', value: '2' },
                                { label: 'Dritte', value: '3' },
                                { label: 'Vierte', value: '4' },
                                { label: 'Fünfte', value: '5' },
                                { label: 'Letzte', value: 'last' },
                            ],
                            onChange: function (val) { setAttrs({ monthlyWeek: val }); }
                        }),
                        attrs.repeatType === 'monthly' && el(SelectControl, {
                            label: 'Wochentag im Monat',
                            value: attrs.monthlyWeekday,
                            options: [
                                { label: 'Montag', value: 'MO' },
                                { label: 'Dienstag', value: 'DI' },
                                { label: 'Mittwoch', value: 'MI' },
                                { label: 'Donnerstag', value: 'DO' },
                                { label: 'Freitag', value: 'FR' },
                                { label: 'Samstag', value: 'SA' },
                                { label: 'Sonntag', value: 'SO' },
                            ],
                            onChange: function (val) { setAttrs({ monthlyWeekday: val }); }
                        }),
                        el(TextControl, {
                            label: 'Ort',
                            value: attrs.location,
                            onChange: function (val) { setAttrs({ location: val }); }
                        }),
                        el(components.ToggleControl, {
                            label: 'Abgelaufene Termine anzeigen',
                            checked: attrs.showExpiredText,
                            onChange: function(val) { setAttrs({ showExpiredText: val }); }
                        }),
                        attrs.showExpiredText && el(TextControl, {
                            label: 'Text für abgelaufene Termine',
                            value: attrs.expiredText,
                            onChange: function(val) { setAttrs({ expiredText: val }); }
                        }),
                    )
                ),
                el('div', {},
                    'Serientermin: ',
                    attrs.startDate, ' bis ', attrs.endDate, ' ',
                    attrs.timeFrom, ' - ', attrs.timeTo, ' ',
                    attrs.repeatType, ' alle ', attrs.repeatEvery,
                    attrs.repeatType === 'weekly' ? ' am ' + attrs.weekday : '',
                   attrs.repeatType === 'monthly'
    ? ' am ' +
      (
        attrs.monthlyWeek === 'last'
          ? 'letzten'
          : (
              { '1': 'ersten', '2': 'zweiten', '3': 'dritten', '4': 'vierten', '5': 'fünften' }[attrs.monthlyWeek] || attrs.monthlyWeek + '.'
            )
      ) +
      ' ' +
      (
        { MO: 'Montag', TU: 'Dienstag', WE: 'Mittwoch', TH: 'Donnerstag', FR: 'Freitag', SA: 'Samstag', SU: 'Sonntag' }[attrs.monthlyWeekday] || attrs.monthlyWeekday
      ) +
      ' im Monat'
    : '',
                    attrs.repeatType === 'yearly' ? ' jährlich' : '',
                    attrs.location ? ' | Ort: ' + attrs.location : ''
                )
            );
        },
        save: function () {
            return null; // Ausgabe per PHP
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);