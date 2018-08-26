﻿<%@ Page Language="C#" MasterPageFile="~/MasterPage/PageTemplate.Master" AutoEventWireup="true" CodeBehind="SelectArea.aspx.cs" Inherits="SfSoft.web.emc.common.SelectArea" %>



<asp:Content ID="Content1" ContentPlaceHolderID="PageBody" runat="server">
    <asp:ScriptManager ID="ScriptManager1" runat="server" EnablePartialRendering="true" />
    <asp:Panel ID="Panel1" runat="server" GroupingText="已选区域" Height="75px" Width="100%">
        &nbsp;&nbsp;&nbsp;
        <asp:TextBox ID="txtSelect" runat="server" Rows="3" TextMode="MultiLine" Width="680px" ></asp:TextBox>
        <asp:HiddenField ID="hfSelect" runat="server" />
        <asp:HiddenField ID="hfSelectTxt" runat="server" />
    </asp:Panel>
    <div class="toolbars">
        <br />
    </div>
    <cc1:TabOptionWebControls ID="TabOption1" runat="server">
        <cc1:TabOptionItem ID="Tab1" runat="server" Tab_Name="地区选择">
            <asp:Panel ID="Panel2" runat="server" Height="210px" Width="100%" ScrollBars="Auto">
                <div id="arealistDiv" runat="server">
                </div>
            </asp:Panel>
        </cc1:TabOptionItem>
    </cc1:TabOptionWebControls>&nbsp;
    <asp:HiddenField ID="hfObjID" runat="server" />
    <asp:HiddenField ID="hfOID" runat="server" />
    <asp:HiddenField ID="hfObjhfID" runat="server" />
    <asp:HiddenField ID="hfFlag" runat="server" />
    <asp:HiddenField ID="hfMode" runat="server" />
    <script type="text/javascript" language="Javascript">

        var ObjPSelectName = parent.document.getElementById("<%=hfObjID.Value %>");
        var ObjPSelectID = parent.document.getElementById("<%=hfObjhfID.Value %>");
        var ObjSelect = document.getElementById("<%=txtSelect.ClientID %>");
        var ObjSelectID = document.getElementById("<%=hfSelect.ClientID %>");
        var ObjSelectTxt = document.getElementById("<%=hfSelectTxt.ClientID %>");
        var ObjHfMode = document.getElementById("<%=hfMode.ClientID %>");

        function SelectUDData(bl, DeptID) {

            var OSelectTD = document.getElementById("tdUD" + DeptID);

            var tmpTD = OSelectTD.getElementsByTagName("input")
            for (var i = 0; i < tmpTD.length; i++) {
                var ckvalue = "";
                var arrValue = "";
                var Uid = "";
                var CnName = "";
                if (tmpTD[i].type == "checkbox") {
                    if (bl == "true") {
                        tmpTD[i].checked = true;
                    }
                    else {
                        tmpTD[i].checked = false;
                    }

                    ckvalue = tmpTD[i].value;
                    arrValue = ckvalue.split('-');
                    Uid = arrValue[0];
                    CnName = arrValue[1];
                    SelectData('ckU' + Uid,  Uid, CnName)
                }
            }

        }
        function SelectData(ObjCkSelect, AssignID, AssignName) {
            var OSelect = document.getElementById(ObjCkSelect);

            var SelectID1 = ObjSelectID.value;
            var Select1 = ObjSelect.value;



            if (OSelect.checked == true) {
                if (SelectID1 == "") {
                    SelectID1 = AssignID;
                    Select1 = AssignName;
                }
                else {
                    if (SelectID1.indexOf(AssignID) == "-1") {
                        SelectID1 = SelectID1 + "," + AssignID
                        Select1 = Select1 + "," + AssignName
                    }
                }
            }
            else {
                AssignID = "," + AssignID + ",";
                SelectID1 = "," + SelectID1 + ",";
                SelectID1 = SelectID1.replace(AssignID, ",");
                AssignName = "," + AssignName + ",";
                Select1 = "," + Select1 + ",";
                Select1 = Select1.replace(AssignName, ",");
                if (SelectID1 != "") {
                    SelectID1 = SelectID1.substr(1, SelectID1.length - 2);
                }
                if (Select1 != "") {
                    Select1 = Select1.substr(1, Select1.length - 2);
                }
            }
            ObjSelect.value = Select1;
            ObjSelectID.value = SelectID1;
            ObjSelectTxt.value = Select1;
        }

        function ClearMember() {
            ObjSelect.value = "";
            ObjSelectID.value = "";
            ObjSelectTxt.value = "";
        }
        function SelectMember() {


            var SelectID = ObjSelectID.value;
            var Select1 = ObjSelect.value;

            ObjPSelectName.value = Select1;
            ObjPSelectID.value = SelectID;
            parent.ClosePop();

        }


        InitSelect();
        function InitSelect() {

            var arrPSelectID = "";
            if (ObjHfMode.value == "") {
                var PSelectName = ObjPSelectName.value;
                var PSelectID = ObjPSelectID.value;
                ObjSelect.value = PSelectName;
                ObjSelectID.value = PSelectID;
                ObjSelectTxt.value = PSelectName;
            }
            else {
                PSelectID = ObjSelectID.value;
            }
            if (PSelectID != "") {
                arrPSelectID = PSelectID.split(',');
                for (i = 0; i < arrPSelectID.length; i++) {
                    var AssignType = arrPSelectID[i].substr(0, 1);
                    var AssignID = arrPSelectID[i].substr(2, arrPSelectID[i].length - 1);
                    var ObjSelected = document.getElementById("ck" + AssignType + AssignID);
                    if (ObjSelected != null) {
                        ObjSelected.checked = "true";
                    }

                }
            }

        }
  
  
    </script>
</asp:Content>