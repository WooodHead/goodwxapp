THREE.MTLLoader=function(a){this.manager=void 0!==a?a:THREE.DefaultLoadingManager};
Object.assign(THREE.MTLLoader.prototype,THREE.EventDispatcher.prototype,{load:function(a,d,c,g){var f=this,b=new THREE.XHRLoader(this.manager);b.setPath(this.path);b.load(a,function(a){d(f.parse(a))},c,g)},setPath:function(a){this.path=a},setTexturePath:function(a){this.texturePath=a},setBaseUrl:function(a){console.warn("THREE.MTLLoader: .setBaseUrl() is deprecated. Use .setTexturePath( path ) for texture path or .setPath( path ) for general base path instead.");this.setTexturePath(a)},setCrossOrigin:function(a){this.crossOrigin=
a},setMaterialOptions:function(a){this.materialOptions=a},parse:function(a){var d=a.split("\n"),c={},g=/\s+/;a={};for(var f=0;f<d.length;f++){var b=d[f],b=b.trim();if(0!==b.length&&"#"!==b.charAt(0)){var h=b.indexOf(" "),e=0<=h?b.substring(0,h):b,e=e.toLowerCase(),b=0<=h?b.substring(h+1):"",b=b.trim();"newmtl"===e?(c={name:b},a[b]=c):c&&("ka"===e||"kd"===e||"ks"===e?(b=b.split(g,3),c[e]=[parseFloat(b[0]),parseFloat(b[1]),parseFloat(b[2])]):c[e]=b)}}d=new THREE.MTLLoader.MaterialCreator(this.texturePath||
this.path,this.materialOptions);d.setCrossOrigin(this.crossOrigin);d.setManager(this.manager);d.setMaterials(a);return d}});THREE.MTLLoader.MaterialCreator=function(a,d){this.baseUrl=a||"";this.options=d;this.materialsInfo={};this.materials={};this.materialsArray=[];this.nameLookup={};this.side=this.options&&this.options.side?this.options.side:THREE.FrontSide;this.wrap=this.options&&this.options.wrap?this.options.wrap:THREE.RepeatWrapping};
THREE.MTLLoader.MaterialCreator.prototype={constructor:THREE.MTLLoader.MaterialCreator,setCrossOrigin:function(a){this.crossOrigin=a},setManager:function(a){this.manager=a},setMaterials:function(a){this.materialsInfo=this.convert(a);this.materials={};this.materialsArray=[];this.nameLookup={}},convert:function(a){if(!this.options)return a;var d={},c;for(c in a){var g=a[c],f={};d[c]=f;for(var b in g){var h=!0,e=g[b],k=b.toLowerCase();switch(k){case "kd":case "ka":case "ks":this.options&&this.options.normalizeRGB&&
(e=[e[0]/255,e[1]/255,e[2]/255]),this.options&&this.options.ignoreZeroRGBs&&0===e[0]&&0===e[1]&&0===e[1]&&(h=!1)}h&&(f[k]=e)}}return d},preload:function(){for(var a in this.materialsInfo)this.create(a)},getIndex:function(a){return this.nameLookup[a]},getAsArray:function(){var a=0,d;for(d in this.materialsInfo)this.materialsArray[a]=this.create(d),this.nameLookup[d]=a,a++;return this.materialsArray},create:function(a){void 0===this.materials[a]&&this.createMaterial_(a);return this.materials[a]},createMaterial_:function(a){var d=
this.materialsInfo[a],c={name:a,side:this.side},g=function(a,b){return"string"!==typeof b||""===b?"":/^https?:\/\//i.test(b)?b:a+b},f;for(f in d){var b=d[f];if(""!==b)switch(f.toLowerCase()){case "kd":c.color=(new THREE.Color).fromArray(b);break;case "ks":c.specular=(new THREE.Color).fromArray(b);break;case "map_kd":if(c.map)break;c.map=this.loadTexture(g(this.baseUrl,b));c.map.wrapS=this.wrap;c.map.wrapT=this.wrap;break;case "ns":c.shininess=parseFloat(b);break;case "d":1>b&&(c.opacity=b,c.transparent=
!0);break;case "Tr":0<b&&(c.opacity=1-b,c.transparent=!0);break;case "map_bump":case "bump":c.bumpMap||(c.bumpMap=this.loadTexture(g(this.baseUrl,b)),c.bumpMap.wrapS=this.wrap,c.bumpMap.wrapT=this.wrap)}}this.materials[a]=new THREE.MeshPhongMaterial(c);return this.materials[a]},loadTexture:function(a,d,c,g,f){var b=THREE.Loader.Handlers.get(a),h=void 0!==this.manager?this.manager:THREE.DefaultLoadingManager;null===b&&(b=new THREE.TextureLoader(h));b.setCrossOrigin&&b.setCrossOrigin(this.crossOrigin);
a=b.load(a,c,g,f);void 0!==d&&(a.mapping=d);return a}};