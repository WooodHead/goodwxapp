﻿<%@ Page Language="C#" MasterPageFile="~/MasterPage/FrameTemplate.Master" AutoEventWireup="true"
    Codebehind="list.aspx.cs" Inherits="SfSoft.web.emc.zxs.theme.list" Title="无标题页" %>
 
<asp:Content ID="Content1" ContentPlaceHolderID="PageBody" runat="server">
     <script type="text/javascript" language="javascript" src="/js/comm.js"></script>
    <cc1:TabOptionWebControls ID="TabOptionWebControls1" runat="server">
        <cc1:TabOptionItem ID="TabOptionItem1" runat="server" Tab_Name="基础数据">
            <table border="0" cellpadding="0" cellspacing="0" style="width:98%" class="fromtable">
                <tr>
                    <td  >
                        主题&nbsp;</td>
                    <td  >
                        <asp:TextBox ID="txtTheme" runat="server" Width="120px"></asp:TextBox></td>
                    <td  >
                        周期&nbsp;</td>
                    <td  >
                        <asp:TextBox ID="txtWeeks" onkeyup="javascript:permitInt(this);" runat="server" Width="120px"></asp:TextBox></td>
                    <td  >
                        顺序号&nbsp;</td>
                    <td   >
                        <asp:TextBox ID="txtSn" runat="server" Width="30px"  onkeyup="javascript:permitInt(this);"></asp:TextBox></td>
                    <td  >
                        是否启用<asp:CheckBox ID="cbIsAct" runat="server" Checked="True" /></td>
                    <td  >
                        &nbsp;<asp:Button ID="btnAdd" runat="server" class="btn" onmouseover="this.className='btn_mouseover'" onmouseout="this.className='btn'" OnClick="btnAdd_Click" Text="增加" /></td>
                </tr>
            </table>
      
                <asp:SmartGridView ID="GridView1" runat="server" AutoGenerateColumns="False" Width="98%" SkinID ="sgv2" 
                    OnRowDeleting="GridView1_RowDeleting" OnRowEditing="GridView1_RowEditing" OnRowUpdating="GridView1_RowUpdating"
                    OnRowCancelingEdit="GridView1_RowCancelingEdit" OnRowDataBound="GridView1_RowDataBound"
                    DataKeyNames="Id" AllowPaging="True" PageSize="50"
                      OnPageIndexChanging="GridView1_PageIndexChanging">
                    <Columns>
                        <asp:BoundField DataField="Id" HeaderText="ID" ReadOnly="True">
                            <ItemStyle Width="20px" />
                        </asp:BoundField>
                        <asp:BoundField DataField="Title" HeaderText="主题">
                            <ItemStyle Width="100px" />
                        </asp:BoundField>
                        <asp:BoundField DataField="Weeks" HeaderText="周期">
                            <ItemStyle Width="100px" />
                        </asp:BoundField>
                        <asp:BoundField DataField="Sn" HeaderText="顺序号">
                            <ItemStyle Width="50px" />
                        </asp:BoundField>
                        <asp:BoundField DataField="StartWeek" HeaderText="起始周">
                            <ItemStyle Width="50px" />
                        </asp:BoundField>
                        <asp:BoundField DataField="EndWeek" HeaderText="终止周">
                            <ItemStyle Width="50px" />
                        </asp:BoundField>
                        <asp:TemplateField HeaderText="启用">
                            <ItemTemplate>
                                <asp:CheckBox ID="cbIsActv" runat="server" />
                                <asp:HiddenField ID="hfIsActv" runat="server" Value='<%# Eval("IsAct") %>' />
                            </ItemTemplate>
                            <ItemStyle Width="50px" />
                        </asp:TemplateField>
                        <asp:CommandField ShowEditButton="True">
                            <ItemStyle Width="50px" />
                        </asp:CommandField>
                        <asp:CommandField ShowDeleteButton="True">
                            <ItemStyle Width="50px" />
                        </asp:CommandField>
                    </Columns>
                    <PagerSettings Position="Top"/>
                    <EditRowStyle Width="50px" />
                </asp:SmartGridView>
            <asp:HiddenField ID="hfClassID" runat="server" />
        </cc1:TabOptionItem>
    </cc1:TabOptionWebControls>
</asp:Content>