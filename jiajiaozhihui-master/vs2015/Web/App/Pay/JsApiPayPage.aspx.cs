﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.UI;
using System.Web.UI.WebControls;
using WxPayAPI;
using SfSoft.web.App;
using System.Web.Services;

namespace WxPayAPI
{
    public partial class JsApiPayPage : System.Web.UI.Page
    {
        private string openId = "";
        private string product_id = "";
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {

                if (string.IsNullOrEmpty(Request["product_id"])) {
                    Response.Write("./msg/error_load.html");
                }
                product_id= Request["product_id"];
                hfProductId.Value = product_id;
                JsApiPay jsApiPay = new JsApiPay(this, product_id);
                try
                {
                    //调用【网页授权获取用户信息】接口获取用户的openid和access_token
                   jsApiPay.GetOpenidAndAccessToken();
                    /*配置jssdk*/
                    OAuth oauth = new OAuth(this.Page);
                    wxConfig = oauth.GetJsSdkConfig();
                    /*获取默认地址*/
                    openId = jsApiPay.openid;
                    hfOpenId.Value = openId;
                }
                catch (Exception ex)
                {
                    Response.Write("./msg/error_load.html");
                }
                try
                {
                    SfSoft.web.Product.Helper.ProductProvide provide = new SfSoft.web.Product.Helper.ProductProvide(int.Parse(product_id));
                    productInfo = provide.GetProductInfo();
                    if(productInfo==null) throw new Exception();
                    //产品其它的设置
                    SetArea = Newtonsoft.Json.JsonConvert.SerializeObject(productInfo.ProductAttach,
                        new Newtonsoft.Json.JsonSerializerSettings() { ContractResolver = new SfSoft.web.App.Helper.UnderlineSplitContractResolver() });
                }
                catch (Exception ex) { 
                    Response.Write("./msg/error_load.html");
                }
            }
        }
        /// <summary>
        /// 产品信息
        /// </summary>
        public SfSoft.web.Product.Models.Product.ProductInfo productInfo { get; set; }
        /// <summary>
        /// jssdk配置信息
        /// </summary>
        public static string  wxConfig{get;set;}
        /// <summary>
        /// 设置不同区域 
        /// </summary>
        public static string SetArea { get; set; }
    }
}