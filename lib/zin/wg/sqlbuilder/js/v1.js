window.builderUpdate = function(action, type = 'page')
{
    let {url, data, selectors} = getSqlBuilderPost(action, type);
    data = {sqlbuilder: data};
    const formData = zui.createFormData({action, data: JSON.stringify(data)});
    const defaultSelectors = ['#sqlBuilder', 'pageJS/.zin-page-js', 'pageCSS/.zin-page-css>*', '#configJS'];
    if(selectors?.length) defaultSelectors.push(selectors);
    postAndLoadPage(url, formData, defaultSelectors.join(','));
}

window.getSqlBuilderPost = function(action, type = 'page')
{
    const sqlBuilder = $('#sqlBuilder').data('sqlbuilder');
    const url        = $('#builderPanel').data('url');
    const selectors  = type == 'page' ? '#builderPanel' : '';

    return {url, data: sqlBuilder, selectors};
}

window.switchBuilderStep = function(event, selectedClass, defaultClass)
{
    const step = $(event.currentTarget).data('step');
    const currentStep = getSqlBuilder('step');
    if(currentStep == step) return;

    setSqlBuilder('step', step);
    builderUpdate('sqlBuilder-step');
}

window.changeBuilderTable = function(event)
{
    const $target = $(event.target);
    const name    = $target.attr('name');
    const value   = $target.val();
    const from    = getSqlBuilder('from');
    const joins   = getSqlBuilder('joins');

    if(name == 'from')
    {
        from.table  = value;
        from.select = [];
    }
    if(name.startsWith('left'))
    {
        const [key, alias] = name.split('_');
        const index = joins.findIndex(join => join.alias == alias);
        if(index < 0) return;

        joins[index].table  = value;
        joins[index].select = [];
    }
    if(name.startsWith('join'))
    {
        const [key, alias, onKey] = name.split('_');
        const index = joins.findIndex(join => join.alias == alias);
        if(index < 0) return;

        if(onKey == 'table')  joins[index].on[0] = value;
        if(onKey == 'fieldA') joins[index].on[1] = value;
        if(onKey == 'fieldB') joins[index].on[4] = value;
    }

    setSqlBuilder('from', from);
    setSqlBuilder('joins', joins);
    builderUpdate('sqlBuilder-table');
}

window.changeBuilderFunc = function(event)
{
    const $target = $(event.target);
    const name    = $target.attr('name');
    const value   = $target.val();
    const funcs   = getSqlBuilder('funcs');

    const [key, index] = name.split('_');

    funcs[index][key] = value;
    setSqlBuilder('funcs', funcs);
    builderUpdate('sqlBuilder-func');
}

window.changeBuilderWhere = function(event)
{
    const $target = $(event.target);
    const name    = $target.attr('name');
    const value   = $target.val();
    const wheres  = getSqlBuilder('wheres');

    const [key, groupIndex, itemIndex] = name.split('_');

    if(key == 'operator')
    {
        wheres[groupIndex].operator = value;
    }
    else
    {
        wheres[groupIndex].items[itemIndex][key] = value;
    }

    setSqlBuilder('wheres', wheres);
    builderUpdate('sqlBuilder-where', key == 4 ? 'ajax' : 'page');
}

window.changeBuilderQuery = function(event)
{
    const $target = $(event.target);
    const name    = $target.attr('name');
    const value   = $target.val();
    const querys  = getSqlBuilder('querys');

    let [key, index] = name.split('_');

    if(index.indexOf("[") !== -1) index = index.substring(0, index.indexOf("["));

    querys[index][key] = value;

    setSqlBuilder('querys', querys);
    builderUpdate('sqlBuilder-query');
}

window.addJoinTable = function(event)
{
    const $target = $(event.target);
    let index     = $(event.currentTarget).data('index');
    const joins   = getSqlBuilder('joins');

    if(index == -1) index = joins.length - 1;
    joins.splice(index + 1, 0, 'add');

    setSqlBuilder('joins', joins);
    builderUpdate('sqlBuilder-table');
}

window.removeJoinTable = function(event)
{
    let index = $(event.currentTarget).data('index');
    const joins   = getSqlBuilder('joins');
    joins.splice(index, 1);

    setSqlBuilder('joins', joins);
    builderUpdate('sqlBuilder-table');
}

window.handleSelectFieldChange = function(event)
{
    const $target = $(event.target);
    const alias   = $target.data('alias');
    const value   = $target.val();
    const checked = $target.is(':checked');
    const from    = getSqlBuilder('from');
    const joins   = getSqlBuilder('joins');

    if(alias == 't1')
    {
        const fieldIndex = from.select.findIndex(field => value == field);

        if(checked) from.select.splice(-1, 0, value);
        else        from.select.splice(fieldIndex, 1);
    }
    else
    {
        const joinIndex  = joins.findIndex(join => join.alias == alias);
        const fieldIndex = joins[joinIndex].select.findIndex(field => value == field);

        if(checked) joins[joinIndex].select.splice(-1, 0, value);
        else        joins[joinIndex].select.splice(fieldIndex, 1);
    }

    setSqlBuilder('from', from);
    setSqlBuilder('joins', joins);
    builderUpdate('sqlBuilder-field', 'ajax');
}

window.checkAllField = function(event)
{
    const alias        = $(event.currentTarget).data('alias');
    const isCheckedAll = $(event.currentTarget).data('checked');
    const from         = getSqlBuilder('from');
    const joins        = getSqlBuilder('joins');

    if(alias == 't1')
    {
        from.select = isCheckedAll ? [] : '*';
    }
    else
    {
        const joinIndex = joins.findIndex(join => join.alias == alias);
        joins[joinIndex].select = isCheckedAll ? [] : '*';
    }

    setSqlBuilder('from', from);
    setSqlBuilder('joins', joins);
    builderUpdate('sqlBuilder-field');
}

window.addFunction = function(event)
{
    const $target = $(event.target);
    let index     = $(event.currentTarget).data('index');
    const funcs   = getSqlBuilder('funcs');

    if(index == -1) index = funcs.length - 1;
    funcs.splice(index + 1, 0, 'add');

    setSqlBuilder('funcs', funcs);
    builderUpdate('sqlBuilder-func');
}

window.removeFunction = function(event)
{
    let index   = $(event.currentTarget).data('index');
    const funcs = getSqlBuilder('funcs');

    funcs.splice(index, 1);

    setSqlBuilder('funcs', funcs);
    builderUpdate('sqlBuilder-func');
}

window.addWhereGroup = function(event)
{
    const $target = $(event.target);
    let index     = $(event.currentTarget).data('index');
    const wheres  = getSqlBuilder('wheres');
    console.log(index);

    if(index == -1) index = wheres.length - 1;
    wheres.splice(index + 1, 0, 'add');

    setSqlBuilder('wheres', wheres);
    builderUpdate('sqlBuilder-group');
}

window.removeWhereGroup = function(event)
{
    let index    = $(event.currentTarget).data('index');
    const wheres = getSqlBuilder('wheres');

    wheres.splice(index, 1);

    setSqlBuilder('wheres', wheres);
    builderUpdate('sqlBuilder-where');
}

window.addWhereItem = function(event)
{
    const $target = $(event.target);
    const index  = $(event.currentTarget).data('index');
    const wheres = getSqlBuilder('wheres');
    const [groupIndex, itemIndex] = index.split('_');

    wheres[groupIndex].items.splice(itemIndex + 1, 0, 'add');

    setSqlBuilder('wheres', wheres);
    builderUpdate('sqlBuilder-where');
}

window.removeWhereItem = function(event)
{
    const index  = $(event.currentTarget).data('index');
    const wheres = getSqlBuilder('wheres');
    const [groupIndex, itemIndex] = index.split('_');

    wheres[groupIndex].items.splice(itemIndex, 1);
    if(!wheres[groupIndex].items.length) wheres.splice(groupIndex, 1);

    setSqlBuilder('wheres', wheres);
    builderUpdate('sqlBuilder-where');
}

window.addBuilderQueryFilter = function(event)
{
    const $target = $(event.target);
    let index     = $(event.currentTarget).data('index');
    const querys  = getSqlBuilder('querys');

    if(index == -1) index = querys.length - 1;
    querys.splice(index + 1, 0, 'add');

    setSqlBuilder('querys', querys);
    builderUpdate('sqlBuilder-query');
}

window.removeBuilderQueryFilter = function(event)
{
    const index  = $(event.currentTarget).data('index');
    const querys = getSqlBuilder('querys');

    querys.splice(index, 1);

    setSqlBuilder('querys', querys);
    builderUpdate('sqlBuilder-query');
}

window.changeGroupBy = function(event)
{
    const $target = $(event.target);
    const checked = $target.is(':checked');
    let groups  = getSqlBuilder('groups');

    groups = checked;

    setSqlBuilder('groups', groups);
    builderUpdate('sqlBuilder-group');
}

window.changeAgg = function(event)
{
    const $target = $(event.target);
    const name    = $target.attr('name');
    const value   = $target.val();
    let groups  = getSqlBuilder('groups');

    const [table, field] = name.split('_');

    groups = groups.map(group => {
        const [groupTable, groupField] = group.select;
        if(table == groupTable && field == groupField) group.select[3] = value;
        return group;
    });

    setSqlBuilder('groups', groups);
    builderUpdate('sqlBuilder-group');
}

window.sortGroupBy = function(event)
{
    const $sortableList = $(event.target).closest('.group-by-sort').zui('SortableList');
    let groups        = getSqlBuilder('groups');
    const sortOrders    = $sortableList.$.getOrders();

    const groupFields = groups.filter(group => group.type == 'group');

    const oldOrders = groupFields.map(group => group.order);
    const originOrders = oldOrders;
    originOrders.sort();
    const newOrders = sortOrders.map(sortOrder => oldOrders[sortOrder]);

    groups = groups.map((group, index) => {
        if(group.type != 'group') return group;

        const newIndex = newOrders.findIndex(order => group.order == order);
        group.order = originOrders[newIndex];
        return group;
    });

    setSqlBuilder('groups', groups);
    builderUpdate('sqlBuilder-group', 'ajax');
}

window.switchGroupFieldType = function(event)
{
    const $target = $(event.target);
    const index   = $(event.currentTarget).data('index');
    const type    = $(event.currentTarget).data('type');
    let groups  = getSqlBuilder('groups');

    if(type == groups[index].type) return;

    groups[index].type = type;

    setSqlBuilder('groups', groups);
    builderUpdate('sqlBuilder-group');
}

window.setSqlBuilder = function(key, value)
{
    const sqlBuilder = $('#sqlBuilder').data('sqlbuilder');
    sqlBuilder[key] = value;
    $('#sqlBuilder').attr('data-sqlbuilder', JSON.stringify(sqlBuilder));
}

window.getSqlBuilder = function(key)
{
    const onUpdate = $('#builderPanel').data('onupdate');
    if(onUpdate?.length) window.builderUpdate = window[onUpdate];

    const sqlBuilder = $('#sqlBuilder').data('sqlbuilder');

    return sqlBuilder[key];
}
