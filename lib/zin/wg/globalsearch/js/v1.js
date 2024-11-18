/**
 * Get the search Type according to the current module and the current method.
 *
 * @access public
 * @return string
 */
window.getGlobalSearchType = function()
{
    if(vision == 'lite') return 'story';

    const appInfo = $.apps.getLastApp();
    if(!appInfo) return;

    const pageWindow = appInfo.iframe.contentWindow;
    if(pageWindow.globalSearchType)
    {
        if(typeof pageWindow.globalSearchType === 'function') return pageWindow.globalSearchType();
        return pageWindow.globalSearchType;
    }

    const moduleName = pageWindow.config.rawModule;
    const methodName = pageWindow.config.rawMethod;

    if(moduleName == 'product' && methodName == 'browse') return 'story';
    if(moduleName == 'my' || moduleName == 'user') return methodName;

    const projectMethod = 'task|story|bug|build';
    if(moduleName == 'project' && projectMethod.indexOf(methodName) != -1) return methodName;
    if(moduleName == 'execution' && projectMethod.indexOf(methodName) != -1) return methodName;

    if(!this.props.items.some(x => x.key === moduleName)) return 'bug';

    if(moduleName == 'search') return '';
    return moduleName;
}

function getSearchUrl(searchType, searchValue, callback)
{
    if(typeof searchValue !== 'string') return;
    searchValue = searchValue.trim();
    if(!searchValue.length) return;

    let searchUrl = $.createLink('search', 'index');
    searchUrl += (searchUrl.indexOf('?') >= 0 ? '&' : '?') + 'words=' + encodeURIComponent(searchValue) + '&type=all';
    if(!searchType || searchType == 'all' || searchType == 'search' || /[^0-9]/.test(searchValue)) return callback(searchUrl);

    const types = searchType.split('-');
    const searchModule = types[0];
    const searchMethod = typeof(types[1]) == 'undefined' ? 'view' : types[1];
    searchUrl = $.createLink(searchModule, searchMethod, "id=" + searchValue);
    const assetType = ',story,issue,risk,opportunity,doc,';

    if(edition != 'max' || assetType.indexOf(',' + searchModule + ',') == -1) return callback(searchUrl);

    const link = $.createLink('index', 'ajaxGetViewMethod' , 'objectID=' + searchValue + '&objectType=' + searchModule);
    $.get(link, function(data)
    {
        if(data)
        {
            callback($.createLink('assetlib', data, "id=" + searchValue));
        }
        else
        {
            return callback(searchUrl);
        }
    });
}

window.handleGlobalSearch = function(type, search)
{
    if(type == 'program')    type = 'program-product';
    if(type == 'deploystep') type = 'deploy-viewstep';
    if(type == 'feedback' && vision != 'lite') type = 'feedback-adminView';

    getSearchUrl(type, search, searchUrl => openUrl(searchUrl));
};
