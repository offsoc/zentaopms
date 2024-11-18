window.handleClickBatchBtn = function($this)
{
    const dtable = zui.DTable.query($this);
    const checkedList = dtable.$.getChecks();
    if(!checkedList.length) return;

    const tabType  = $this.data('type');

    const url   = $this.data('url');
    const form  = new FormData();
    const field = $this.hasClass('bug-batch-close') ? 'unlinkBugs[]' : `${tabType}IdList[]`;
    checkedList.forEach((id) => form.append(field, id));

    if($this.hasClass('load-btn'))
    {
        postAndLoadPage(url, form);
    }
    else
    {
        $.ajaxSubmit({url, data: form});
    }
};

window.handleLinkObjectClick = function($this)
{
    const type   = $this.data('type');
    const dtable = zui.DTable.query($this);
    const checkedList = dtable.$.getChecks();
    if(!checkedList.length) return;

    const postKey  = type == 'story' ? 'stories' : 'bugs';
    const postData = new FormData();
    checkedList.forEach((id) => postData.append(postKey + '[]', id));

    $.ajaxSubmit({url: $this.data('url'), data: postData});
};

/**
 * 计算表格任务信息的统计。
 * Set task summary for table footer.
 *
 * @param  element element
 * @param  array   checkedidlist
 * @access public
 * @return object
 */
window.setStoryStatistics = function(element, checkedIDList)
{
    const checkedTotal = checkedIDList.length;
    if(checkedTotal == 0) return {html: summary};

    let checkedEstimate = 0;
    let checkedCase     = 0;
    let rateCount       = checkedTotal;

    const rows = element.layout.allRows;
    rows.forEach((row) => {
        if(checkedIDList.includes(row.id))
        {
            const story = row.data;
            const cases = storyCases[row.id];
            checkedEstimate += parseFloat(story.estimate);

            if(cases > 0)
            {
                checkedCase ++;
            }
            else if(story.children != undefined && story.children > 0)
            {
                rateCount --;
            }
        }
    })

    let rate = '0%';
    if(rateCount) rate = Math.round(checkedCase / rateCount * 100) + '%';

    return {html: checkedSummary.replace('%total%', checkedTotal).replace('%estimate%', checkedEstimate.toFixed(1)).replace('%rate%', rate)};
}

/**
 * 移除关联的对象。
 * Remove linked object.
 *
 * @param  sting objectType
 * @param  int   objectID
 * @access public
 * @return void
 */
window.unlinkObject = function(objectType, objectID)
{
    objectType = objectType.toLowerCase();

    zui.Modal.confirm(eval(`confirmunlink${objectType}`)).then((res) => {
        if(res)
        {
            $.ajaxSubmit({url: eval(`unlink${objectType}url`).replace('%s', objectID)});
        }
    });

}

window.showLink = function(type, params, onlyUpdateTable)
{

    let relatedParams = 'releaseID=' + releaseID + (params || '&browseType=&param=');
    let idName        = 'finishedStory';
    if(type == 'bug')
    {
        idName         = 'resolvedBug';
        relatedParams += '&type=bug';
    }
    else if(type == 'leftBug')
    {
        idName         = 'leftBug';
        relatedParams += '&type=leftBug';
    }

    const url = $.createLink(releaseModule, type === 'story' ? 'linkStory' : 'linkBug', relatedParams);
    if(onlyUpdateTable)
    {
        loadComponent($('#' + idName).find('.dtable').attr('id'), {url: url, component: 'dtable', partial: true});
        return;
    }

    loadTarget({url: url, target: idName});
};

$(function()
{
    if(initLink == 'true') window.showLink(type, linkParams);
})

window.onSearchLinks = function(type, result)
{
    const params = $.parseLink(result.load).vars[3];
    showLink(type, params ? atob(params[1]) : null, true);
    return false;
};

window.renderStoryCell = function(result, info)
{
    const story = info.row.data;
    if(info.col.name == 'title' && result)
    {
        let html = '';
        let gradeLabel = (showGrade || story.grade >= 2) ? grades[story.grade] : '';
        if(gradeLabel) html += "<span class='label gray-pale rounded-xl'>" + gradeLabel + "</span>";
        if(html) result.unshift({html});
    }

    return result;
};
