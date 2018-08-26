var app = getApp()
Page({
  data: {
    imagesUrl: app.data.picUrl,
    city: "选择城市",
    // 省市信息
    arrayPro: [],
    provinceInfo: [],
    arrayCity: [],
    cityInfo: [],
    show: true,
    photoURL: app.data.picUrl + "photo.jpg",
    // 预约信息
    yuyue: {},
    // 登录
    showLogin: false,
    // 手机号
    mobile: "",
    mobileUser: "",
    // 验证码
    verifycode: "",
    // openid
    openid: '',
    options: '',
    // 地址
    val: [],
    // 验证码倒计时
    yzm: "获取验证码",
    getCode: "getCode",
    // input  聚焦
    focus: false,
    color: "#621192",
    showrole: true,
    allCity: {}
  },
  // 活动规则
  getRole: function () {
    this.setData({
      showrole: !(this.data.showrole)
    })
  },
  closeRole: function () {
    this.setData({
      showrole: !(this.data.showrole)
    })
  },
  // 确定地址
  confirm: function () {
    if (this.data.val[0] == 0 || this.data.val[0] == 7) {
      this.setData({
        show: (!this.data.show),
      })
    } else {
      this.setData({
        show: (!this.data.show),
        city: this.data.arrayCity[this.data.val[1]],
      })
      wx.setStorage({
        key: 'city',
        data: this.data.arrayCity[this.data.val[1]],
      })
    }
  },
  bindChange: function (e) {
    const val = e.detail.value
    getCity(this, val[0], this.data.provinceInfo, val)
  },

  onLoad: function (options) {
    this.setData({
      options: options
    })
    var that = this
    // 获取位置
    wx.getStorage({
      key: 'city',
      success: function (res) {
        that.setData({
          city: res.data
        })
      },
    })
    // 获取手机号
    wx.getStorage({
      key: 'mobile',
      success: function (res) {
        that.setData({
          mobile: res.data,
          mobileUser: change(res.data)
        })
      }
    })
    wx.getStorage({
      key: 'openid',
      success: function (res) {
        that.setData({
          openid: res.data
        })
        // 获取预约信息
        wx.request({
          url: app.data.requestUrl + 'leilingapplet/public/index.php/index/User/reservationInfomation',
          data: {
            "openid": res.data,
            "orderid": options.orderid,
            "orderh5id": options.orderh5id
          },
          success: function (res) {
            var data = res.data
            var yuyue = {}
            yuyue.dealer_name = data.dealerinfo.dealer_name
            yuyue.address = data.dealerinfo.address
            yuyue.mobile = data.dealerinfo.tel
            yuyue.model = data.orderinfo.model_name
            that.setData({
              yuyue: yuyue
            })

          }
        })
      }
    })
    // 获取省
    wx.request({
      url: app.data.requestUrl + '/leilingapplet/public/index.php/index/User/getProvince',
      success: function (data) {
        var allCity = data.data.city
        var data = data.data.province
        var province = [];
        for (var i = 0; i < data.length; i++) {
          province.push(data[i].province_name)
        }
        that.setData({
          arrayPro: province,
          provinceInfo: data,
          allCity: allCity
        })
      }
    })
  },
  // 换头像
  updatePhoto: function () {
    this.setData({
      showLogin: true
    })
  },
  close2: function () {
    this.setData({
      showLogin: false
    })
  },
  switchCity: function () {
    this.setData({
      show: (!this.data.show)
    })
  },
  // 修改
  update: function () {
    wx.navigateTo({
      url: '../regInfo/regInfo?url=lookInfo&orderid=' + this.data.options.orderid + '&orderh5id=' + this.data.options.orderh5id,
    })

  },
  // 重新预约
  reyuyue: function () {
    var that = this
    wx.showModal({
      title: '重新预约',
      content: ' 重新预约并删除已提交数据',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: app.data.requestUrl + '/leilingapplet/public/index.php/index/User/rereservation',
            method: "POST",
            data: {
              "openid": that.data.openid,
              "orderid": that.options.orderid
            }, success: function (data) {
              if (data.data.error == 0) {
                wx.showToast({
                  title: '重新预约成功',
                })
                setTimeout(function () {
                  wx.reLaunch({
                    url: '../index/index'
                  })
                }, 2000)
              } else {
                wx.showToast({
                  title: '重新预约失败,稍后重试',
                })
              }
            }
          })
        }
      }
    })


  },
  // 提交信息
  submitInfo: function () {
    wx.navigateTo({
      url: '../testDriveInfo/testDriveInfo?orderid=' + this.data.options.orderid,
    })
  },
  // 测试手机号
  testMobile: function (e) {
    this.setData({
      mobile: e.detail.value
    })
  },
  // 测试验证码
  testVerifycode: function (e) {
    this.setData({
      verifycode: e.detail.value
    })
  },
  // 键盘下拉
  keyDown: function (e) {
    var val = e.detail.value
    if (val.length == 4) {
      wx.hideKeyboard()
    }
  },
  // 获取验证码
  getCode: function () {
    var _this = this;
    if (!(/^1[34578]\d{9}$/.test(this.data.mobile))) {
      wx.showToast({
        title: '手机号格式不正确',
        image: '',
        duration: 2000
      })
    } else {
      wx.request({
        url: app.data.requestUrl + 'leilingapplet/public/index.php/index/User/CreateVerificationCode/',
        method: "GET",
        data: { "telephone": _this.data.mobile },
        success: function (d) {
          var data = d.data.verifycode
          if (data == 0) {
            wx.showToast({
              title: '不好意思,请稍后重试',
            })
          } else {
            var index = 59
            _this.setData({
              yzm: index + "s",
              getCode: "",
              focus: true,
              color: "#ccc"
            })
            var timer = setInterval(function () {
              if (index == 1) {
                clearInterval(timer)
                _this.setData({
                  yzm: "获取验证码",
                  getCode: "getCode",
                  color: "#621192"
                })
              } else {
                _this.setData({
                  yzm: --index + "s"
                })
              }
            }, 1000)
          }
        }
      })
    }
  },
  // 账号登录
  user_login: function () {
    var _this = this
    if (this.data.verifycode.length == 0) {
      wx.showToast({
        title: '请输入验证码'
      })
    } else {
      wx.request({
        url: app.data.requestUrl + 'leilingapplet/public/index.php/index/User/userLogin',
        method: "POST",
        data: {
          'telephone': _this.data.mobile,
          'city': _this.data.city,
          'verificationcode': _this.data.verifycode,
          'openid': _this.data.openid
        },
        success: function (d) {
          var data = d.data
          if (data.error == 0) {
            wx.showToast({
              title: data.message,
              image: '',
              duration: 2000
            })
            _this.setData({
              showLogin: false,
              mobileUser: change(_this.data.mobile)
            })
            wx.setStorage({
              key: 'mobile',
              data: _this.data.mobile,
            })
          } else {
            wx.showToast({
              title: data.message,
              image: '',
              duration: 2000
            })
          }
        }
      })
    }
  }
})
// 获取市
function getCity(that, i, data, val) {
  var allCity = that.data.allCity
  var x
  var arr = []
  for (x in allCity) {
    if (x == data[i].province_code) {
      var simpleCity = allCity[x]
      console.log(simpleCity.length)
      for (var k = 0; k < simpleCity.length; k++) {
        arr.push(simpleCity[k].city_name)
      }
    }
  }
  that.setData({
    arrayCity: arr,
    val: val
  })
}

function change(str) {
  var s = ""
  for (var i = 0; i < 3; i++) {
    s += str[i];
  }
  for (var i = 3; i < 7; i++) {
    s += "*"
  }
  for (var i = 7; i < 11; i++) {
    s += str[i]
  }
  return s;
}

