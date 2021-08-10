//var wdObj = require('./wikidata');

const wd = new WikiData();

wd.getItemBatch(['Q211', 'Q16355192', 'Q97234075', 'Q97232999'], () => {
    console.log('this', wd.items)

    Object.keys(wd.items).map(item => {
        const realItem = wd.items[item];
        console.log('lv', realItem.getLabel('lv'))
        console.log('default', realItem.getLabel())
    })
})