﻿using System;
using System.Data;
using System.Configuration;
using System.Collections;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Web.UI.WebControls.WebParts;
using System.Web.UI.HtmlControls;
using SfSoft.Common;
using SfSoft.DBUtility;
using System.Collections.Generic;
using System.Linq;

namespace SfSoft.web.emc.wxcourse.section
{
    public partial class list : SfSoft.SfEmc.EmcBasePage
    {
        private string _pid = "";
        protected void Page_Load(object sender, EventArgs e)
        {
            
            _pid = Request.Params["pid"].Trim();
            if (!IsPostBack)
            {
                string ClassID = Request.Params["ClassID"].Trim();
                
                hfClassID.Value = ClassID;

                BindData(GetWhere());
            }
            SetTabName();
            

        }
        
        //1.初始化模块ID
        protected override void VtInitMID()
        {
            hfMID.Value = "emc.wxcourse.section";
        }
        protected override void VtPageAccess()
        {
            CheckPageAccess("emc.wxcourse.section.browse");
        }
 
        private void SetTabName()
        {
            BLL.WX_Course bllBdc = new BLL.WX_Course();
            Model.WX_Course  model= bllBdc.GetModel(int.Parse(hfClassID.Value));
            string tabname = "基础数据列表";
            if (model!=null)
            {
                
                if (!string.IsNullOrEmpty(_pid))
                {
                    BLL.WX_Course_Section bll = new BLL.WX_Course_Section();
                    var m = bll.GetModel(int.Parse(_pid));
                    tabname = "[" + model.Name + "-" + m.SectionName + "]" + tabname;
                }
                else {
                    tabname = "[" + model.Name + "]"+tabname;
                }
            }
            
            TabOptionItem1.Tab_Name = tabname;
        }

        private void BindData(string strWhere)
        {
            BLL.WX_Course_Section bllBd = new BLL.WX_Course_Section();

            DataSet dsbd = bllBd.GetList(strWhere);
            GridView1.DataSource = dsbd;
            GridView1.DataBind();

            //初始化顺序号
            //int OrderID = DbHelperSQL.GetMaxID("OrderID", "Pub_BaseData", Session["FilialeID"].ToString());
            //this.txtOrderID.Text = OrderID.ToString();
            this.txtOrderID.Text = InitSn().ToString();
        }

        private string GetWhere()
        {
            if (string.IsNullOrEmpty(_pid))
            {
                return " ClassifyId='" + hfClassID.Value + "' and Pid is Null ";
            }
            else {
                return "PId=" + _pid;   
            }
        }

        protected void GridView1_RowEditing(object sender, GridViewEditEventArgs e)
        {
            GridView1.EditIndex = e.NewEditIndex;
            BindData(GetWhere());
        }

        //删除
        protected void GridView1_RowDeleting(object sender, GridViewDeleteEventArgs e)
        {

            BLL.WX_Course_Section bllbd = new BLL.WX_Course_Section();
            int RefID = Common.Common.stringToInt(GridView1.DataKeys[e.RowIndex].Value.ToString());
            bllbd.Delete(RefID);

            BindData(GetWhere());
        }

        //更新
        protected void GridView1_RowUpdating(object sender, GridViewUpdateEventArgs e)
        {
            Model.WX_Course_Section modelbd = new Model.WX_Course_Section();
            BLL.WX_Course_Section bllBd = new BLL.WX_Course_Section();
            int RefID = Common.Common.stringToInt(GridView1.DataKeys[e.RowIndex].Value.ToString());
            modelbd = bllBd.GetModel(RefID);
            modelbd.SectionId = ((TextBox)GridView1.Rows[e.RowIndex].FindControl("txtRefValueCode")).Text.Trim();

            modelbd.SectionName = ((TextBox)(GridView1.Rows[e.RowIndex].Cells[2].Controls[0])).Text.ToString().Trim();
            modelbd.Sn = Common.Common.stringToInt(((TextBox)(GridView1.Rows[e.RowIndex].Cells[3].Controls[0])).Text.ToString().Trim());
             DropDownList ddlSectionType= (DropDownList)(GridView1.Rows[e.RowIndex].FindControl("ddlSectionType"));
             modelbd.SectionType = Convert.ToInt32(ddlSectionType.SelectedValue);
            if (!CheckCodes())
            {
                MessageBox.Show(this, "编码重复，请输入其它编码!");
                return;
            }
            bllBd.Update(modelbd);

            GridView1.EditIndex = -1;
            BindData(GetWhere());
        }

        //取消
        protected void GridView1_RowCancelingEdit(object sender, GridViewCancelEditEventArgs e)
        {
            GridView1.EditIndex = -1;
            BindData(GetWhere());
        }

        //绑定行数据时更新内容
        protected void GridView1_RowDataBound(object sender, GridViewRowEventArgs e)
        {
            if (e.Row.RowType == DataControlRowType.DataRow)
            {
                if (e.Row.RowState == DataControlRowState.Normal || e.Row.RowState == DataControlRowState.Alternate)
                {
                    ((LinkButton)e.Row.Cells[6].Controls[0]).Attributes.Add("onclick", "javascript:return confirm('你确认要删除：\"" + e.Row.Cells[2].Text + "\"吗?')");
                }
                if (e.Row.RowState == DataControlRowState.Edit) {
                    HiddenField hfSectionType = (HiddenField)e.Row.FindControl("hfSectionType");
                    DropDownList ddlSectionType = (DropDownList)e.Row.FindControl("ddlSectionType");
                    if(!string.IsNullOrEmpty(hfSectionType.Value)){
                        ddlSectionType.Items.FindByValue(hfSectionType.Value).Selected = true;
                    }
                }
            }
        }

        protected void btnAdd_Click(object sender, EventArgs e)
        {

            string strErr = "";

            if (this.txtRefValueCode.Text == "")
            {
                strErr += "编码不能为空！\\n";
            }

            if (this.txtRefValue.Text == "")
            {
                strErr += "值不能为空！\\n";
            }

            if (!PageValidate.IsNumber(txtOrderID.Text))
            {
                strErr += "顺序号不是数字！\\n";
            }

            if (strErr != "")
            {
                MessageBox.Show(this, strErr);
                return;
            }
            int RefPID = 0;
            string RefObj = hfClassID.Value;
            string RefValueCode = this.txtRefValueCode.Text;
            if (!CheckCodes())
            {
                MessageBox.Show(this, "编码重复，请输入其它编码!");
                return;
            }
            string RefValue = this.txtRefValue.Text;
            
            if (this.txtOrderID.Text == "")
            {
                this.txtOrderID.Text = "0";
            }
            int OrderID = int.Parse(this.txtOrderID.Text);


            Model.WX_Course_Section model = new Model.WX_Course_Section();
            model.ClassifyId = hfClassID.Value;
            model.SectionId = txtRefValueCode.Text;
            model.SectionName = txtRefValue.Text;
            model.Sn =int.Parse(txtOrderID.Text);
            if (!string.IsNullOrEmpty(_pid)) {
                model.PId = int.Parse(_pid);
            }

            BLL.WX_Course_Section bll = new BLL.WX_Course_Section();
            bll.Add(model);

            this.txtRefValueCode.Text = "";
            this.txtRefValue.Text = "";
            this.txtOrderID.Text = "";
            BindData(GetWhere());
        }

        private Boolean CheckCodes()
        {
            BLL.WX_Course_Section bllbd = new BLL.WX_Course_Section();
            string strWhere = " SectionId = '" + txtRefValueCode.Text + "' and ClassifyId='" + hfClassID.Value + "' and IsNull(PId,'')='" + _pid + "'";
            DataSet dsbd = bllbd.GetList(strWhere);
            if (dsbd.Tables[0].Rows.Count > 0)
            {
                return false;
            }
            else
            {
                return true;
            }

        }
        protected void GridView1_PageIndexChanging(Object sender, GridViewPageEventArgs e)
        {
            GridView1.PageIndex = e.NewPageIndex;
            BindData(GetWhere());
        }

        private int InitSn()
        {
            BLL.WX_Course_Section bll = new BLL.WX_Course_Section();
            DataSet ds= bll.GetList("ClassifyId='"+hfClassID.Value+"' and Isnull(Pid,'')='"+_pid+"'");
            if (ds != null) {
                return ds.Tables[0].Rows.Count;
            }
            return 1;
        }
        public string SectionTypeName(string type)
        {
            string result = "";
            if (type == "1") {
                result = "视频";
            }
            else if (type == "2")
            {
                result = "图文音";
            }
            return result;
        }
    }
}

