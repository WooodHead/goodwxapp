THREE.CSS3DObject=function(h){THREE.Object3D.call(this);this.elementCreator=h;this.elementCache={};this.getElementByRender=function(g){if(null!=this.elementCache[g.id])return this.elementCache[g.id];var k=h();this.elementCache[g.id]=k;k.style.position="absolute";this.addEventListener("removed",function(g){null!==k.parentNode&&k.parentNode.removeChild(k)});return k}};THREE.CSS3DObject.prototype=Object.create(THREE.Object3D.prototype);THREE.CSS3DObject.prototype.constructor=THREE.CSS3DObject;
var css3dRenderInstanceCount=0;
THREE.CSS3DRenderer=function(){this.id=css3dRenderInstanceCount++ +"";console.log("THREE.CSS3DRenderer",THREE.REVISION);var h,g,k,l;new THREE.Matrix4;var m=0,n="",p={},d=document.createElement("div");d.style.overflow="hidden";d.style.WebkitTransformStyle="preserve-3d";d.style.MozTransformStyle="preserve-3d";d.style.oTransformStyle="preserve-3d";d.style.transformStyle="preserve-3d";this.domElement=d;var e=document.createElement("div");e.style.WebkitTransformStyle="preserve-3d";e.style.MozTransformStyle=
"preserve-3d";e.style.oTransformStyle="preserve-3d";e.style.transformStyle="preserve-3d";d.appendChild(e);this.setClearColor=function(){};this.getSize=function(){return{width:h,height:g}};this.setSize=function(b,f){h=b;g=f;k=h/2;l=g/2;d.style.width=b+"px";d.style.height=f+"px";e.style.width=b+"px";e.style.height=f+"px"};var b=function(b){return b.toFixed(6)};this.renderObject=function(d,f){if(d instanceof THREE.CSS3DObject){var a;a=d.matrixWorld.elements;a="translate3d(-50%,-50%,0) matrix3d("+b(a[0])+
","+b(a[1])+","+b(a[2])+","+b(a[3])+","+b(-a[4])+","+b(-a[5])+","+b(-a[6])+","+b(-a[7])+","+b(a[8])+","+b(a[9])+","+b(a[10])+","+b(a[11])+","+b(a[12])+","+b(a[13])+","+b(a[14])+","+b(a[15])+")";var c=d.getElementByRender(this),g=p[d.id];if(void 0===g||g!==a)c.style.WebkitTransform=a,c.style.MozTransform=a,c.style.oTransform=a,c.style.transform=a,p[d.id]=a;c.parentNode!==e&&e.appendChild(c)}a=0;for(c=d.children.length;a<c;a++)this.renderObject(d.children[a],f)};this.render=function(h,f){var a=.5/Math.tan(THREE.Math.degToRad(.5*
f.getEffectiveFOV()))*g;m!==a&&(d.style.WebkitPerspective=a+"px",d.style.MozPerspective=a+"px",d.style.oPerspective=a+"px",d.style.perspective=a+"px",m=a);h.updateMatrixWorld();null===f.parent&&f.updateMatrixWorld();f.matrixWorldInverse.getInverse(f.matrixWorld);var c;c=f.matrixWorldInverse.elements;c="matrix3d("+b(c[0])+","+b(-c[1])+","+b(c[2])+","+b(c[3])+","+b(c[4])+","+b(-c[5])+","+b(c[6])+","+b(c[7])+","+b(c[8])+","+b(-c[9])+","+b(c[10])+","+b(c[11])+","+b(c[12])+","+b(-c[13])+","+b(c[14])+","+
b(c[15])+")";a="translate3d(0,0,"+a+"px)"+c+" translate3d("+k+"px,"+l+"px, 0)";n!==a&&(e.style.WebkitTransform=a,e.style.MozTransform=a,e.style.oTransform=a,n=e.style.transform=a);this.renderObject(h,f)}};