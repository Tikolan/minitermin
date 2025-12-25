(function (blocks, element) {
    var el = element.createElement;
    blocks.registerBlockType('minitermin/ical-link', {
        title: 'MiniTermin iCal-Link',
        icon: 'calendar',
        category: 'widgets',
        edit: function () {
            return el('div', {}, 'Im Frontend erscheint ein iCal-Link für alle Termine dieses Beitrags.');
        },
        save: function () {
            return null; // PHP rendert
        }
    });
})(
    window.wp.blocks,
    window.wp.element
);