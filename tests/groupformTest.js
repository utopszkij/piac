const { execPath, hasUncaughtExceptionCaptureCallback } = require('process');
const inc = require('../vendor/viewTester.js');
inc.mock();

loadView('./includes/views/groupform.html', (vue) => {
    vue.errorMsg = '';
    vue.record = {"id":1, "name":"test1"};
    vue.loged = 2;
    vue.logedAdmin = true;
    vue.previous = "https://example.hu/?task=userek";
    
    describe('groupForm', () => {
       it ('vue szintaktika test', () => {
		   var w = vueTest(vue);		
	       expect(w).toBeTruthy();
       });
       it ('delClick szintaktika test', () => {
	       vue.delClick();
	       expect(vue.errorMsg).toEqual('');
       });
    });
});
