window.getSpaceType = function()
{
    return $('.modal-body [name=rootSpace]:checked').val();
}

/**
 * Change space.
 *
 * @access public
 * @return void
 */
window.changeSpace = function()
{
    const objectType = getSpaceType();
    if(objectType) loadModal($.createLink('doc', 'selectLibType', `objectType=${objectType}`));
}

/**
 * Reload select mineandcustom page.
 *
 * @access public
 * @return void
 */
window.reloadMineAndCustom = function()
{
    const objectType = getSpaceType();
    const objectID   = $(`.modal-body input[name=${objectType}]`).val();
    const libID      = $('.modal-body input[name=lib]').val();
    const params     = window.btoa('objectID=' + objectID + '&libID=' + libID);

    loadModal($.createLink('doc', 'selectLibType', `objectType=${objectType}&params=${params}`));
}

/**
 * Reload select product page.
 *
 * @access public
 * @return void
 */
window.reloadProduct = function()
{
    const objectType = getSpaceType();
    const docType    = $('.modal-body input[name=type]:checked').val();
    const objectID   = $('.modal-body input[name=product]').val();
    const libID      = $('.modal-body input[name=lib]').val();
    const params     = window.btoa('docType=' + docType + '&objectID=' + objectID + '&libID=' + libID);

    loadModal($.createLink('doc', 'selectLibType', `objectType=${objectType}&params=${params}`));
}

/**
 * Reload select project page.
 *
 * @access public
 * @return void
 */
window.reloadProject = function()
{
    const objectType  = getSpaceType();
    const docType     = $('.modal-body input[name=type]:checked').val();
    const objectID    = $('.modal-body input[name=project]').val();
    const executionID = $('.modal-body input[name=execution]').val();
    const libID       = $('.modal-body input[name=lib]').val();
    const params      = window.btoa('docType=' + docType + '&objectID=' + objectID + '&executionID=' + executionID + '&libID=' + libID);

    loadModal($.createLink('doc', 'selectLibType', `objectType=${objectType}&params=${params}`));
}

/**
 * Reload select api page by apiType.
 *
 * @access public
 * @return void
 */
window.reloadApiByApiType = function()
{
    const objectType  = getSpaceType();
    const apiType     = $('.modal-body input[name=apiType]').val();
    const params      = window.btoa('apiType=' + apiType);
    loadModal($.createLink('doc', 'selectLibType', `objectType=${objectType}&params=${params}`));
}

/**
 * Reload select api page.
 *
 * @access public
 * @return void
 */
window.reloadApi = function()
{
    const objectType  = getSpaceType();
    const apiType     = $('.modal-body input[name=apiType]').val();
    const libID       = $('.modal-body input[name=lib]').val();

    let params = '';
    if(apiType == 'product')
    {
        const objectID = $('.modal-body input[name=product]').val();
        params = window.btoa('apiType=' + apiType + '&objectID=' + objectID + '&libID=' + libID);
    }
    else if(apiType == 'project')
    {
        const objectID    = $('.modal-body input[name=project]').val();
        const executionID = $('.modal-body input[name=execution]').val();
        params = window.btoa('apiType=' + apiType + '&objectID=' + objectID + '&executionID=' + executionID + '&libID=' + libID);
    }
    else if(apiType == 'nolink')
    {
        params = window.btoa('apiType=' + apiType + '&libID=' + libID);
    }

    loadModal($.createLink('doc', 'selectLibType', `objectType=${objectType}&params=${params}`));
}

/**
 * Redirect the parent window.
 *
 * @param  string objectType
 * @param  int    libID
 * @param  string docType
 * @access public
 * @return void
 */
window.redirectParentWindow = function(link)
{
    openUrl(link, 'doc');
}
