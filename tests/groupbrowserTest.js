const { execPath, hasUncaughtExceptionCaptureCallback } = require('process');
const inc = require('../vendor/viewTester.js');
inc.mock();

loadView('./includes/views/groupbrowser.html', (vue) => {
    vue.errorMsg = '';
	vue.successMsg = '';
	vue.items = [{"id":1,"name":"test"}];
    vue.logedAdmin = true;
    vue.siteurl = 'index.php';
	
    describe('groupBrowser', () => {
       it ('vue test', () => {
		   var w = vueTest(vue);		
	       expect(w).toBeTruthy();
       });
       it ('makePaginatorClass', () => {
	       var w = vue.makePaginatorClass(2,2);
	       expect(vue.errorMsg).toEqual('');
	       expect(w).toEqual('actPaginatorItem');
       });
     });
});
