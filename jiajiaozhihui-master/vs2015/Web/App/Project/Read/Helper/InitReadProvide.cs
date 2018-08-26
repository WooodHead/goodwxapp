﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using SfSoft.web.Read.Enums;

namespace SfSoft.web.Read.Helper
{
    public class InitReadProvide
    {
        public static SfSoft.Model.WX_Read_Info GetReadInfo(string appId)
        {
            SfSoft.BLL.WX_Read_Info bll = new SfSoft.BLL.WX_Read_Info();
            return bll.GetModelList("").AsEnumerable().FirstOrDefault(e => e.AppId == appId);
        }
        public static SfSoft.Model.WX_Read_User GetReadUserInfo(string appId, string openId)
        {
            SfSoft.BLL.WX_Read_User bll = new SfSoft.BLL.WX_Read_User();
            return bll.GetModelList("AppId='" + appId + "' and OpenId='" + openId + "'").AsEnumerable().FirstOrDefault();
        }
        public static SfSoft.Model.WX_UserInfo GetWxUserInfo(string openId)
        {
            SfSoft.BLL.WX_UserInfo bll = new SfSoft.BLL.WX_UserInfo();
            return bll.GetModelList("openId='" + openId + "'").AsEnumerable().FirstOrDefault();
        }
        public static Models.Award.AwardInfo  GetAwardList(string openId)
        {
            SfSoft.BLL.WX_Read_Award bll = new BLL.WX_Read_Award();
            Models.Award.AwardInfo awardInfo = new Models.Award.AwardInfo();
             var list= bll.GetModelList("OpenId='" + openId + "'");
             if (list.Count != 0)
             {
                 var diamond = list.Find(e => e.AwardType == (int)EnumAwardType.钻石);
                 awardInfo.Diamond = diamond == null ? 0 : diamond.Earn ?? 0;

                 var gold = list.Find(e => e.AwardType == (int)EnumAwardType.金币);
                 awardInfo.Gold = gold == null ? 0 : gold.Earn ?? 0;

                 var integral=list.Find(e => e.AwardType == (int)EnumAwardType.积分);
                 awardInfo.Integral = integral == null ? 0 : integral.Earn ?? 0;

                 Model.WX_Read_Grade grade = GetGradeList(awardInfo.Integral);
                 awardInfo.Grade = grade.Grade ?? 1;
                 awardInfo.GradeName = grade.GradeName;
             }
             return awardInfo;
        }
        public static void RegistUser(string appId, string openId)
        {
            var wxUser = GetWxUserInfo(openId);
            if (wxUser != null) {
                var readUser = GetReadUserInfo(appId, openId);
                if (readUser == null) {
                    SfSoft.BLL.WX_Read_User bll = new SfSoft.BLL.WX_Read_User();
                    SfSoft.Model.WX_Read_User model = new Model.WX_Read_User();
                    model.AppId = appId;
                    model.IsAct = 1;
                    model.OpenId = openId;
                    model.RegionDate = DateTime.Now;
                    model.UserType = 1;
                    bll.Add(model);
                }
            }
        }
        public static bool IsAttention(string openId)
        {
            Senparc.Weixin.MP.AdvancedAPIs.User.UserInfoJson result = Senparc.Weixin.MP.AdvancedAPIs.UserApi.Info(App.Helper.WxBaseConfig.AppID, openId);
            return result.subscribe == 0 ? false : true;
        }
        private static SfSoft.Model.WX_Read_Grade GetGradeList(int integral)
        {
            SfSoft.BLL.WX_Read_Grade bll = new BLL.WX_Read_Grade();
            var model= bll.GetModelList("" + integral.ToString() + " between LowerLimit and UpperLimit").FirstOrDefault() ;
            if (model != null)
            {
                return model;
            }
            else {
                return new Model.WX_Read_Grade();
            }
        }
    }
}