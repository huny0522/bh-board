function StateRoute(){
	/** @var {{path : string, api : string|null, ignoreSubPath : boolean, search : string[], hashCheck : boolean, matchAction : function, noMatchAction : function, isAllSearch : boolean}[]} **/
	this.routeData = [];
	this.beforeUrl = '';

	this.id = '';
	this.element = null;
};

StateRoute._IsWaitPlus = function(){
	StateRoute.IsWait++;
	StateRoute.LoadedShow(StateRoute.IsWait);
}

StateRoute._IsWaitMinus = function(){
	StateRoute.IsWait--;
	StateRoute.LoadedShow(StateRoute.IsWait);
}

StateRoute.LoadedShow = function(num){
	if(document.getElementById('loading_layer')){
		document.getElementById('loading_layer').style.display = num ? 'block' : 'none';
	}
}

StateRoute.prototype._Init = function(elementId, element){
	this.id = elementId;
	this.element = element;
	this.beforeUrl = location.pathname + location.search + location.hash;

	if(typeof StateRoute.eventInit === 'undefined'){
		StateRoute.eventInit = true;
		window.addEventListener('popstate', (e) => {
			StateRoute._LocationChanged();
		});
	}
}
StateRoute.IsWait = 0;

/**
 * @param {string} elementId
 * @returns {StateRoute|null}
 * @constructor
 */
StateRoute.Get = function(elementId){
	const el = document.getElementById(elementId);
	if(!el){
		console.error('해당 아이디의 객체가 없습니다.', elementId);
		delete this;
		return null;
	}

	if(typeof StateRoute.routes === 'undefined') StateRoute.routes = [];
	for(let i = 0; i < StateRoute.routes.length; i++){
		if(elementId === StateRoute.routes[i].id){
			return StateRoute.routes[i];
		}
	}

	const obj = new StateRoute();
	obj._Init(elementId, el);
	StateRoute.routes.push(obj);
	return obj;
}

/**
 *
 * @param element
 * @returns {null|StateRoute}
 * @constructor
 */
StateRoute.GetRoute = function(element){
	for(let i = 0; i < StateRoute.routes.length; i++){
		if((typeof(element) === 'string' && element === StateRoute.routes[i].id) || element === StateRoute.routes[i].element) return StateRoute.routes[i];
	}
	return null;
}

StateRoute.SetWrapLink = function(elementId){
	const wrap = document.getElementById(elementId);
	if(!wrap){
		console.error('Not find link wrap.');
		return;
	}
	wrap.addEventListener('click', (e) => {
		const a = e.target.tagName === 'A' ? e.target : e.target.closest('a');
		if(a && a.tagName === 'A'){
			if(a.getAttribute('class') && /button/.test(a.getAttribute('class'))) return;
			e.preventDefault();
			if(a.pathname + a.search + a.hash == location.pathname + location.search + location.hash) return;

			history.pushState({}, '', a.href);
			StateRoute._LocationChanged();
		}
	});
}

StateRoute.StateChangeAction = function(func){
	if(typeof(StateRoute.stateChangeActionFunc) === 'undefined') StateRoute.stateChangeActionFunc = [];
	StateRoute.stateChangeActionFunc.push(func);
}

StateRoute.StatePreviousChangeAction = function(func){
	if(typeof(StateRoute.stateChangePreActionFunc) === 'undefined') StateRoute.stateChangePreActionFunc = [];
	StateRoute.stateChangePreActionFunc.push(func);
}

StateRoute.PushState = function(url){
	const a = document.createElement('a');
	a.href = url;
	if(location.href === a.href) history.replaceState({}, '', url);
	else history.pushState({}, '', url);
	StateRoute._LocationChanged();
}

StateRoute.ReplaceState = function(url){
	history.replaceState({}, '', url);
	StateRoute._LocationChanged();
}

StateRoute.Alert = function(message, func){
	alert(message);
	if(typeof func === 'function') func();
}

StateRoute._LocationChanged = function(){
	if(typeof(StateRoute.stateChangePreActionFunc) !== 'undefined'){
		for(let i = 0; i < StateRoute.stateChangePreActionFunc.length; i++){
			StateRoute.stateChangePreActionFunc[i]();
		}
	}
	for(let i = StateRoute.routes.length - 1; i >= 0; i--){
		if(!document.getElementById(StateRoute.routes[i].id)){
			StateRoute.routes[i].Del();
			StateRoute.routes.splice(i, 1);
		}
		else StateRoute.routes[i].PageLoaded();
	}
	if(typeof(StateRoute.stateChangeActionFunc) !== 'undefined'){
		for(let i = 0; i < StateRoute.stateChangeActionFunc.length; i++){
			StateRoute.stateChangeActionFunc[i]();
		}
	}
}

StateRoute.StateChangeForm = function(form){
	if(typeof form === 'string') form = document.querySelector(form);
	if(form.tagName !== 'FORM'){
		console.error('ajaxForm:form 객체가 아닙니다.')
		return false;
	}
	let formData = new FormData(form);
	let data = {};
	formData.forEach((value, key) => {
		if(/\[\]$/g.test(key)){
			key = key.replace(/\[\]$/g, '');
			if(typeof (data[key]) === 'undefined') data[key] = [];
			data[key].push(value);
		}
		else if(value !== '') data[key] = value;
	});
	let body = [];
	for(const [idx, val] of Object.entries(data)){
		if(Array.isArray(val)){
			for(let i = 0; i < val.length; i++){
				body.push(idx + '[]=' + encodeURIComponent(val[i]));
			}
		}
		else body.push(idx + '=' + encodeURIComponent(val));
	}

	history.pushState({a : 'a'}, '', form.action + (body.length ? (form.action.indexOf('?') >= 0 ? '&' : '?') + body.join('&') : ''));
	StateRoute._LocationChanged();
}

StateRoute.SubStr = function(str, start, num){
	if(start < 0) start = str.length + start;
	if(typeof num === 'undefined') num = str.length;
	else if(num < 0) num = str.length + num;
	else num = start + num;
	return str.substring(start, num);
}

StateRoute.Ajax = async function(url, method, data){
	let opt = {
		method : method,
		cache : 'no-cache',
		credentials: 'same-origin',
		mode: 'cors'
	};

	if(typeof(data) === 'undefined') data = {};
	if(method.toLowerCase() === 'post'){
		opt.body = new URLSearchParams(data);
		opt.headers = {
			'Content-Type': 'application/x-www-form-urlencoded',
			'Accept' : 'application/json'
		};
	}
	else{
		const newData = {};
		const entries = data.constructor.name === 'FormData' ? data.entries() : Object.entries(data);
		for(const [idx, val] of entries){
			if(Array.isArray(val)){
				for(let i = 0; i < val.length; i++){
					newData[idx] = val[i];
				}
			}
			else newData[idx] = val;
		}
		url = url + (url.indexOf('?') >= 0 ? '&' : '?') + new URLSearchParams(newData).toString();
		opt.headers = {
			'Content-Type': 'application/json'
		};
	}
	const res = await fetch(url, opt);
	if(res.ok){
		try{
			const json = await res.json();
			if(typeof(json.result) !== 'undefined' && json.result === false) throw new Error(JSON.stringify(json));
			else return json;
		}
		catch(e){
			try{
				const errorJson = JSON.parse(e.message);
			}
			catch(e2){
				throw new Error('서버에서 정보를 불러오지 못했습니다.');
			}
			throw new Error(e.message);
		}
	}
	else{
		throw new Error(await res.text());
	}
}

StateRoute.AjaxForm = function(form){
	if(typeof form === 'string') form = document.querySelector(form);
	if(form.tagName !== 'FORM'){
		console.error('ajaxForm:form 객체가 아닙니다.')
		return false;
	}
	return StateRoute._AjaxGetOrPost(form.action, form.method.toUpperCase(), new FormData(form));
}

StateRoute.__onceDuplicate = [];
StateRoute.AjaxFormOnce = async function(form){
	if(typeof form === 'string') form = document.querySelector(form);
	if(form.tagName !== 'FORM'){
		console.error('ajaxForm:form 객체가 아닙니다.')
		return false;
	}

	return await StateRoute._AjaxOnce(form.action, form.method.toUpperCase(), new FormData(form), form);
}

/**
 *
 * @param {string} url
 * @param {{}} data
 * @param {string=} opt
 * @returns {Promise<*>}
 */
StateRoute.AjaxPostOnce = async function(url, data, onceKey){
	return await StateRoute._AjaxOnce(url, 'POST', data, onceKey);
}

/**
 *
 * @param {string} url
 * @param {{}} data
 * @param {string=} opt
 * @returns {Promise<*>}
 */
StateRoute.AjaxGetOnce = async function(url, data, onceKey){
	return await StateRoute._AjaxOnce(url, 'GET', data, onceKey);
}

StateRoute._AjaxOnce = async function(url, method, data, onceKey){
	if(typeof onceKey === 'undefined') onceKey = method + ':' + url + JSON.stringify(data);

	for(const [k, v] of StateRoute.__onceDuplicate.entries()){
		if(v === onceKey) return;
	}
	StateRoute.__onceDuplicate.push(onceKey);
	const res = await StateRoute._AjaxGetOrPost(url, method, data);
	for(const [k, v] of StateRoute.__onceDuplicate.entries()){
		if(v === onceKey) StateRoute.__onceDuplicate.splice(k, 1);
	}
	return res;
}

StateRoute.AjaxGet = function(url, data){
	return StateRoute._AjaxGetOrPost(url, 'GET', data);
}

StateRoute.AjaxPost = function(url, data){
	return StateRoute._AjaxGetOrPost(url, 'POST', data);
}

StateRoute._AjaxGetOrPost = function(url, method, data){
	StateRoute._IsWaitPlus();
	return StateRoute.Ajax(url, method, data).then(r => {
		StateRoute._IsWaitMinus();
		if(typeof(r.message) !== 'undefined' && r.message.length) StateRoute.Alert(r.message);
		return r;
	}).catch(r => {
		StateRoute._IsWaitMinus();
		if(typeof(r.message) !== 'undefined' && r.message.length){
			try{
				const errorJson = JSON.parse(r.message);
				if(typeof(errorJson.message) !== 'undefined' && errorJson.message !== '') StateRoute.Alert(errorJson.message);
				return errorJson;
			}
			catch(e2){
				StateRoute.Alert(r.message);
			}
		}
		return r;
	});
}

/**
 * @param {{path : string, api : string|null=, search : string|null=, hashCheck : boolean=, noMatchAction : function=, matchAction : function=, ignoreSubPath : boolean=, withSub : boolean=, isAllSearch : boolean=}} param
 * <pre>
 *     ignoreSubPath : URL이 변경되도 화면에 고정되는 페이지임.
 *     hashCheck : 해시 변경을 감지
 *     matchAction : location.pathname과 일치할때 실행 될 함수
 *     noMatchAction : location.pathname과 매치되지 않을 때 실행 될 함수
 * </pre>
 * @returns {StateRoute}
 * @constructor
 */
StateRoute.prototype.AddRouteAction = function(param){
	for(let i = 0; i < this.routeData.length; i++){
		if(param.path === this.routeData[i].path) return this;
	}
	if(typeof param.api !== 'string') param.api = null;
	param.search = typeof param.search !== 'string' ? [] : param.search.split(',');
	if(typeof param.hashCheck !== 'boolean') param.hashCheck = false;
	if(typeof param.noMatchAction !== 'function') param.noMatchAction = null;
	if(typeof param.matchAction !== 'function') param.matchAction = null;
	if(typeof param.ignoreSubPath !== 'boolean') param.ignoreSubPath = false;
	if(typeof param.withSub !== 'boolean') param.withSub = false;
	if(typeof param.isAllSearch !== 'boolean') param.isAllSearch = false;
	if(param.withSub === true){
		let param2 = {};
		for(const [idx, obj] of Object.entries(param)){
			param2[idx] = obj;
		}
		if(StateRoute.SubStr(param2.path, -1) !== '/') param2.path += '/';
		param2.path += '*';
		if(StateRoute.SubStr(param2.api, -1) !== '/') param2.api += '/';
		param2.api += '*';

		this.routeData.push(param2);
	}
	this.routeData.push(param);
	return this;
}

StateRoute.prototype.View = function(){
	this.PageLoaded(true);
}

StateRoute.prototype.PageLoaded = function(force){
	const uri = location.pathname + location.search + location.hash;
	const a = document.createElement('a');
	a.href = this.beforeUrl;
	const beforeSearch = a.search;
	const beforePath = a.pathname;
	const beforeHash = a.hash;
	a.remove();
	this.beforeUrl = uri;
	const nowPath = location.pathname;
	const nowSearch = location.search;
	const nowHash = location.hash;

	for(let i = 0; i < this.routeData.length; i++){
		let isChanged = false;
		let isMatch = false;
		let isBeforeMatch = false;
		const rData = this.routeData[i];
		let path = rData.path;
		const isWildcard = StateRoute.SubStr(rData.path, -1) === '*';
		if(isWildcard) path = StateRoute.SubStr(path, 0, -1);
		const chkRoutePath = StateRoute.SubStr(path, -1) == '/' ? path : path + '/';
		const chkNowPath = StateRoute.SubStr(nowPath, -1) == '/' ? nowPath : nowPath + '/';
		const chkBeforePath = StateRoute.SubStr(beforePath, -1) == '/' ? beforePath : beforePath + '/';

		if(isWildcard){
			if(StateRoute.SubStr(chkNowPath, 0, chkRoutePath.length) === chkRoutePath && chkNowPath !== chkRoutePath) isMatch = true;
			if(StateRoute.SubStr(chkBeforePath, 0, chkRoutePath.length) === chkRoutePath && chkNowPath !== chkRoutePath) isBeforeMatch = true;
		}
		else if(rData.ignoreSubPath){
			if(StateRoute.SubStr(chkNowPath, 0, chkRoutePath.length) === chkRoutePath) isMatch = true;
			if(StateRoute.SubStr(chkBeforePath, 0, chkRoutePath.length) === chkRoutePath) isBeforeMatch = true;
		}
		else{
			if(chkNowPath === chkRoutePath) isMatch = true;
			if(chkBeforePath === chkRoutePath) isBeforeMatch = true;
		}
		if(chkNowPath === '/' && path === '/') isMatch = true;
		if(chkBeforePath === '/' && path === '/') isBeforeMatch = true;

		if(isMatch){
			// step 1. path check
			if(!isBeforeMatch) isChanged = true;
			else if(isWildcard && chkNowPath !== chkBeforePath) isChanged = true;

			// step 2. search check
			const b_s = this._SearchParse(beforeSearch);
			const n_s = this._SearchParse(location.search);
			if(rData.isAllSearch){
				for(const [idx, val] of Object.entries(b_s)){
					if(n_s[idx] !== val) isChanged = true;
				}
				for(const [idx, val] of Object.entries(n_s)){
					if(b_s[idx] !== val) isChanged = true;
				}
			}
			else{
				for(let s = 0; s < rData.search.length; s++){
					const k = rData.search[s];
					if((typeof(n_s[k]) !== 'undefined' && typeof(b_s[k]) === 'undefined')
						|| (typeof(n_s[k]) === 'undefined' && typeof(b_s[k]) !== 'undefined')
						|| (typeof(n_s[k]) !== 'undefined' && typeof(b_s[k]) !== 'undefined' && b_s[k] !== n_s[k])){
						isChanged = true;
					}
				}
			}
			// step 3. hash check
			if(rData.hashCheck && beforeHash !== location.hash){
				isChanged = true;
			}
		}

		if(((force === true && isMatch == true) || isChanged) && rData.api){
			let api = rData.api;
			const apiIsWildcard = StateRoute.SubStr(api, -1) === '*';
			if(apiIsWildcard) api = StateRoute.SubStr(api, 0, -1);
			if(isWildcard && apiIsWildcard && StateRoute.SubStr(location.pathname, path.length - 1) !== '') api = StateRoute.SubStr(api, 0, -1) + StateRoute.SubStr(location.pathname, path.length - 1);
			this.ApiCall(this.element, api + location.search, rData);
		}

		if(isMatch){
			if(rData.matchAction !== null) rData.matchAction(this);
		}
		else if(rData.noMatchAction !== null) rData.noMatchAction(this);
	}
};

StateRoute.prototype.ApiCall = function(element, path){
	const _this = this;
	StateRoute.AjaxGet(path, {statePage : 1}).then((res) => {
		element.innerHTML = typeof(res.data) === 'string' ? res.data : res.data.html;
		let newScr = [];
		const scr = element.getElementsByTagName('script');
		while(scr.length){
			newScr.push(_this._CloneNode(scr.item(0)));
			scr.item(0).remove();
		}
		for(let i = 0; i < newScr.length; i++){
			element.appendChild(newScr[i]);
		}
	}).catch((res) => {

	});
}

StateRoute.prototype._CloneNode = function(node){
	const script  = document.createElement('script');
	script.text = node.innerHTML;

	var i = -1, attrs = node.attributes, attr;
	while ( ++i < attrs.length ) {
		script.setAttribute( (attr = attrs[i]).name, attr.value );
	}
	return script;
}

StateRoute.prototype._SearchParse = function(search){
	let s = search[0] === '?' ? search.substring(1) : search;
	if(s === '') return {};
	return JSON.parse('{"' + decodeURI(s).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g,'":"').replace(/\t/g,'\\t') + '"}');
}


StateRoute.prototype.Del = function(){
	this.element.remove();
	this.element = null;
	delete this;
}

let customEvent = new CustomEvent('stateroute_ready');
window.dispatchEvent(customEvent);
