module("vizengine_core.js",function(){
    var Object = com.vizengine.core.Object;

    /**
     * @class com.vizengine.math.Matrix4
     * 矩阵
     */
    Object.extend("com.vizengine.math.Matrix4",{
        init : function() {
            this.elements = [];
            this.identity();
        },
        set : function(n11, n12, n13, n14, n21, n22, n23, n24, n31, n32, n33, n34, n41, n42, n43, n44) {
            var te = this.elements;

            te[ 0 ] = n11; te[ 4 ] = n12; te[ 8 ] = n13; te[ 12 ] = n14;
            te[ 1 ] = n21; te[ 5 ] = n22; te[ 9 ] = n23; te[ 13 ] = n24;
            te[ 2 ] = n31; te[ 6 ] = n32; te[ 10 ] = n33; te[ 14 ] = n34;
            te[ 3 ] = n41; te[ 7 ] = n42; te[ 11 ] = n43; te[ 15 ] = n44;

            return this;
        },
        identity: function () {
            this.set(
                1, 0, 0, 0,
                0, 1, 0, 0,
                0, 0, 1, 0,
                0, 0, 0, 1
            );
            return this;
        },
        fromArray: function ( array, offset ) {
            if ( offset == null ){
                offset = 0;
            }
            for( var i = 0; i < 16; i ++ ) {
                this.elements[ i ] = array[ i + offset ];
            }
            return this;
        },
        toArray: function ( array, offset ) {
            if ( array == null ) array = [];
            if ( offset == null ) offset = 0;
            var te = this.elements;
            array[ offset ] = te[ 0 ];
            array[ offset + 1 ] = te[ 1 ];
            array[ offset + 2 ] = te[ 2 ];
            array[ offset + 3 ] = te[ 3 ];

            array[ offset + 4 ] = te[ 4 ];
            array[ offset + 5 ] = te[ 5 ];
            array[ offset + 6 ] = te[ 6 ];
            array[ offset + 7 ] = te[ 7 ];

            array[ offset + 8 ]  = te[ 8 ];
            array[ offset + 9 ]  = te[ 9 ];
            array[ offset + 10 ] = te[ 10 ];
            array[ offset + 11 ] = te[ 11 ];

            array[ offset + 12 ] = te[ 12 ];
            array[ offset + 13 ] = te[ 13 ];
            array[ offset + 14 ] = te[ 14 ];
            array[ offset + 15 ] = te[ 15 ];
            return array;
        },
        equals: function ( matrix ) {
            var te = this.elements;
            var me = matrix.elements;
            for ( var i = 0; i < 16; i ++ ) {
                if ( te[ i ] !== me[ i ] ) return false;
            }
            return true;
        },
        clone: function () {
            return new com.vizengine.math.Matrix4().fromArray( this.elements );
        },
        copyFrom: function ( m ) {
            var te = this.elements;
            var me = m.elements;
            for ( var i = 0; i < 16; i ++ ) te[ i ] = me[ i ];
            return this;
        },
        /**
         * a.multiply(b)  <=>  a*=b
         * a.mulitply(b,c) <=> a=b*c
         * @returns {*}
         */
        multiply: function () {
            var a,b;
            if(arguments.length == 1) {
                a = this;
                b = arguments[0];
            } else if(arguments.length == 2) {
                a = arguments[0];
                b = arguments[1];
            } else {
                return null;
            }
            var ae = a.elements;
            var be = b.elements;
            var te = this.elements;

            var a11 = ae[ 0 ], a12 = ae[ 4 ], a13 = ae[ 8 ], a14 = ae[ 12 ];
            var a21 = ae[ 1 ], a22 = ae[ 5 ], a23 = ae[ 9 ], a24 = ae[ 13 ];
            var a31 = ae[ 2 ], a32 = ae[ 6 ], a33 = ae[ 10 ], a34 = ae[ 14 ];
            var a41 = ae[ 3 ], a42 = ae[ 7 ], a43 = ae[ 11 ], a44 = ae[ 15 ];

            var b11 = be[ 0 ], b12 = be[ 4 ], b13 = be[ 8 ], b14 = be[ 12 ];
            var b21 = be[ 1 ], b22 = be[ 5 ], b23 = be[ 9 ], b24 = be[ 13 ];
            var b31 = be[ 2 ], b32 = be[ 6 ], b33 = be[ 10 ], b34 = be[ 14 ];
            var b41 = be[ 3 ], b42 = be[ 7 ], b43 = be[ 11 ], b44 = be[ 15 ];

            te[ 0 ] = a11 * b11 + a12 * b21 + a13 * b31 + a14 * b41;
            te[ 4 ] = a11 * b12 + a12 * b22 + a13 * b32 + a14 * b42;
            te[ 8 ] = a11 * b13 + a12 * b23 + a13 * b33 + a14 * b43;
            te[ 12 ] = a11 * b14 + a12 * b24 + a13 * b34 + a14 * b44;

            te[ 1 ] = a21 * b11 + a22 * b21 + a23 * b31 + a24 * b41;
            te[ 5 ] = a21 * b12 + a22 * b22 + a23 * b32 + a24 * b42;
            te[ 9 ] = a21 * b13 + a22 * b23 + a23 * b33 + a24 * b43;
            te[ 13 ] = a21 * b14 + a22 * b24 + a23 * b34 + a24 * b44;

            te[ 2 ] = a31 * b11 + a32 * b21 + a33 * b31 + a34 * b41;
            te[ 6 ] = a31 * b12 + a32 * b22 + a33 * b32 + a34 * b42;
            te[ 10 ] = a31 * b13 + a32 * b23 + a33 * b33 + a34 * b43;
            te[ 14 ] = a31 * b14 + a32 * b24 + a33 * b34 + a34 * b44;

            te[ 3 ] = a41 * b11 + a42 * b21 + a43 * b31 + a44 * b41;
            te[ 7 ] = a41 * b12 + a42 * b22 + a43 * b32 + a44 * b42;
            te[ 11 ] = a41 * b13 + a42 * b23 + a43 * b33 + a44 * b43;
            te[ 15 ] = a41 * b14 + a42 * b24 + a43 * b34 + a44 * b44;

            return this;
        },
        premultiply: function ( m ) {
            return this.multiply( m, this );
        },
        translate : function(x,y,z) {
            return this.multiply(new Matrix4().setTranslation(x,y,z));
        },
        rotateX : function(rotate) {
            return this.multiply(new Matrix4().setRotationX(rotate));
        },
        rotateY : function(rotate) {
            return this.multiply(new Matrix4().setRotationY(rotate));
        },
        rotateZ : function(rotate) {
            return this.multiply(new Matrix4().setRotationZ(rotate));
        },
        scale : function(x,y,z) {
            return this.multiply(new Matrix4().setScale(x,y,z));
        },
        perspective: function(fov,aspect,near,far) {
            return this.multiply(new Matrix4().setPerspective(fov,aspect,near,far));
        }
        ,
        setTranslation: function ( x, y, z ) {
            this.set(
                1, 0, 0, x,
                0, 1, 0, y,
                0, 0, 1, z,
                0, 0, 0, 1
            );
            return this;
        },
        setRotationX: function ( theta ) {
            var c = Math.cos( theta ), s = Math.sin( theta );
            this.set(
                1, 0,  0, 0,
                0, c, - s, 0,
                0, s,  c, 0,
                0, 0,  0, 1
            );
            return this;
        },
        setRotationY: function ( theta ) {
            var c = Math.cos( theta ), s = Math.sin( theta );
            this.set(
                c, 0, s, 0,
                0, 1, 0, 0,
                - s, 0, c, 0,
                0, 0, 0, 1
            );
            return this;
        },
        setRotationZ: function ( theta ) {
            var c = Math.cos( theta ), s = Math.sin( theta );
            this.set(
                c, - s, 0, 0,
                s,  c, 0, 0,
                0,  0, 1, 0,
                0,  0, 0, 1
            );
            return this;
        },
        setScale: function ( x, y, z ) {
            this.set(
                x, 0, 0, 0,
                0, y, 0, 0,
                0, 0, z, 0,
                0, 0, 0, 1
            );
            return this;
        },
        setFrustum: function ( left, right, top, bottom, near, far ) {
            var te = this.elements;
            var x = 2 * near / ( right - left );
            var y = 2 * near / ( top - bottom );

            var a = ( right + left ) / ( right - left );
            var b = ( top + bottom ) / ( top - bottom );
            var c = - ( far + near ) / ( far - near );
            var d = - 2 * far * near / ( far - near );

            te[ 0 ] = x;	te[ 4 ] = 0;	te[ 8 ] = a;	te[ 12 ] = 0;
            te[ 1 ] = 0;	te[ 5 ] = y;	te[ 9 ] = b;	te[ 13 ] = 0;
            te[ 2 ] = 0;	te[ 6 ] = 0;	te[ 10 ] = c;	te[ 14 ] = d;
            te[ 3 ] = 0;	te[ 7 ] = 0;	te[ 11 ] = - 1;	te[ 15 ] = 0;

            return this;
        },
        /**
         * 设置透视矩阵参数
         * @param fov 广角参数，摄像机上边缘到下边缘与投影点形成的角度，单位为弧度
         * @param aspect 宽高比
         * @param near 近裁剪平面距离
         * @param far 远裁剪平面距离
         * @returns {*}
         */
        setPerspective: function(fov,aspect,near,far) {
            var hh = near * Math.tan(fov/2);
            var top = hh;
            var bottom = -hh;
            var right = hh * aspect;
            var left = -right;
            return this.setFrustum(left,right,top,bottom,near,far);
        },
        setOrthographic: function ( left, right, top, bottom, near, far ) {
            var te = this.elements;
            var w = 1.0 / ( right - left );
            var h = 1.0 / ( top - bottom );
            var p = 1.0 / ( far - near );
            var x = ( right + left ) * w;
            var y = ( top + bottom ) * h;
            var z = ( far + near ) * p;
            te[ 0 ] = 2 * w;	te[ 4 ] = 0;	te[ 8 ] = 0;	te[ 12 ] = - x;
            te[ 1 ] = 0;	te[ 5 ] = 2 * h;	te[ 9 ] = 0;	te[ 13 ] = - y;
            te[ 2 ] = 0;	te[ 6 ] = 0;	te[ 10 ] = - 2 * p;	te[ 14 ] = - z;
            te[ 3 ] = 0;	te[ 7 ] = 0;	te[ 11 ] = 0;	te[ 15 ] = 1;
            return this;
        },
        /**
         * getInverse Matrix4 for current matrix,if fail return null
         * @returns {*}
         */
        getInverse: function () {
            var targetM = new Matrix4();
            // based on http://www.euclideanspace.com/maths/algebra/matrix/functions/inverse/fourD/index.htm
            var te = targetM.elements,
                me = this.elements,

                n11 = me[ 0 ], n21 = me[ 1 ], n31 = me[ 2 ], n41 = me[ 3 ],
                n12 = me[ 4 ], n22 = me[ 5 ], n32 = me[ 6 ], n42 = me[ 7 ],
                n13 = me[ 8 ], n23 = me[ 9 ], n33 = me[ 10 ], n43 = me[ 11 ],
                n14 = me[ 12 ], n24 = me[ 13 ], n34 = me[ 14 ], n44 = me[ 15 ],

                t11 = n23 * n34 * n42 - n24 * n33 * n42 + n24 * n32 * n43 - n22 * n34 * n43 - n23 * n32 * n44 + n22 * n33 * n44,
                t12 = n14 * n33 * n42 - n13 * n34 * n42 - n14 * n32 * n43 + n12 * n34 * n43 + n13 * n32 * n44 - n12 * n33 * n44,
                t13 = n13 * n24 * n42 - n14 * n23 * n42 + n14 * n22 * n43 - n12 * n24 * n43 - n13 * n22 * n44 + n12 * n23 * n44,
                t14 = n14 * n23 * n32 - n13 * n24 * n32 - n14 * n22 * n33 + n12 * n24 * n33 + n13 * n22 * n34 - n12 * n23 * n34;

            var det = n11 * t11 + n21 * t12 + n31 * t13 + n41 * t14;

            if ( det === 0 ) {
                var msg = "Matrix4.getInverse(): can't invert matrix, determinant is 0";
                console.warn( msg );
                return null;
            }

            var detInv = 1 / det;

            te[ 0 ] = t11 * detInv;
            te[ 1 ] = ( n24 * n33 * n41 - n23 * n34 * n41 - n24 * n31 * n43 + n21 * n34 * n43 + n23 * n31 * n44 - n21 * n33 * n44 ) * detInv;
            te[ 2 ] = ( n22 * n34 * n41 - n24 * n32 * n41 + n24 * n31 * n42 - n21 * n34 * n42 - n22 * n31 * n44 + n21 * n32 * n44 ) * detInv;
            te[ 3 ] = ( n23 * n32 * n41 - n22 * n33 * n41 - n23 * n31 * n42 + n21 * n33 * n42 + n22 * n31 * n43 - n21 * n32 * n43 ) * detInv;

            te[ 4 ] = t12 * detInv;
            te[ 5 ] = ( n13 * n34 * n41 - n14 * n33 * n41 + n14 * n31 * n43 - n11 * n34 * n43 - n13 * n31 * n44 + n11 * n33 * n44 ) * detInv;
            te[ 6 ] = ( n14 * n32 * n41 - n12 * n34 * n41 - n14 * n31 * n42 + n11 * n34 * n42 + n12 * n31 * n44 - n11 * n32 * n44 ) * detInv;
            te[ 7 ] = ( n12 * n33 * n41 - n13 * n32 * n41 + n13 * n31 * n42 - n11 * n33 * n42 - n12 * n31 * n43 + n11 * n32 * n43 ) * detInv;

            te[ 8 ] = t13 * detInv;
            te[ 9 ] = ( n14 * n23 * n41 - n13 * n24 * n41 - n14 * n21 * n43 + n11 * n24 * n43 + n13 * n21 * n44 - n11 * n23 * n44 ) * detInv;
            te[ 10 ] = ( n12 * n24 * n41 - n14 * n22 * n41 + n14 * n21 * n42 - n11 * n24 * n42 - n12 * n21 * n44 + n11 * n22 * n44 ) * detInv;
            te[ 11 ] = ( n13 * n22 * n41 - n12 * n23 * n41 - n13 * n21 * n42 + n11 * n23 * n42 + n12 * n21 * n43 - n11 * n22 * n43 ) * detInv;

            te[ 12 ] = t14 * detInv;
            te[ 13 ] = ( n13 * n24 * n31 - n14 * n23 * n31 + n14 * n21 * n33 - n11 * n24 * n33 - n13 * n21 * n34 + n11 * n23 * n34 ) * detInv;
            te[ 14 ] = ( n14 * n22 * n31 - n12 * n24 * n31 - n14 * n21 * n32 + n11 * n24 * n32 + n12 * n21 * n34 - n11 * n22 * n34 ) * detInv;
            te[ 15 ] = ( n12 * n23 * n31 - n13 * n22 * n31 + n13 * n21 * n32 - n11 * n23 * n32 - n12 * n21 * n33 + n11 * n22 * n33 ) * detInv;

            return targetM;
        }
    })
    var Matrix4 = com.vizengine.math.Matrix4;

    Object.extend("com.vizengine.math.Vector3",{
        init : function(x,y,z) {
            this.set(x,y,z);
        },
        set : function(x,y,z) {
            this.x = x == null ? 0 : x;
            this.y = y == null ? 0 : y;
            this.z = z == null ? 0 : z;
        }
        ,
        applyMatrix4 : function(m) {
            var x = this.x, y = this.y, z = this.z;
            var e = m.elements;
            this.x = e[ 0 ] * x + e[ 4 ] * y + e[ 8 ]  * z + e[ 12 ];
            this.y = e[ 1 ] * x + e[ 5 ] * y + e[ 9 ]  * z + e[ 13 ];
            this.z = e[ 2 ] * x + e[ 6 ] * y + e[ 10 ] * z + e[ 14 ];
            var w =  e[ 3 ] * x + e[ 7 ] * y + e[ 11 ] * z + e[ 15 ];
            return this.multiplyScalar( 1 / w );
        },
        multiplyScalar: function ( scalar ) {
            this.x *= scalar;
            this.y *= scalar;
            this.z *= scalar;
            return this;
        }
    })
    var Vector3 = com.vizengine.math.Vector3;
})