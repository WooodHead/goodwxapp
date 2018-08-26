﻿using System;
using System.Data;
namespace SfSoft.IDAL
{
	/// <summary>
	/// 接口层WX_WarrantyCard
	/// </summary>
	public interface IWX_WarrantyCard
	{
		#region  成员方法
		/// <summary>
		/// 是否存在该记录
		/// </summary>
		bool Exists(string MachineCode);
		/// <summary>
		/// 增加一条数据
		/// </summary>
		bool Add(SfSoft.Model.WX_WarrantyCard model);
		/// <summary>
		/// 更新一条数据
		/// </summary>
		bool Update(SfSoft.Model.WX_WarrantyCard model);
		/// <summary>
		/// 删除一条数据
		/// </summary>
		bool Delete(string MachineCode);
		bool DeleteList(string MachineCodelist );
		/// <summary>
		/// 得到一个对象实体
		/// </summary>
		SfSoft.Model.WX_WarrantyCard GetModel(string MachineCode);
		SfSoft.Model.WX_WarrantyCard DataRowToModel(DataRow row);
		/// <summary>
		/// 获得数据列表
		/// </summary>
		DataSet GetList(string strWhere);
		/// <summary>
		/// 获得前几行数据
		/// </summary>
		DataSet GetList(int Top,string strWhere,string filedOrder);
		int GetRecordCount(string strWhere);
		DataSet GetListByPage(string strWhere, string orderby, int startIndex, int endIndex);
		/// <summary>
		/// 根据分页获得数据列表
		/// </summary>
		//DataSet GetList(int PageSize,int PageIndex,string strWhere);
		#endregion  成员方法
		#region  MethodEx

		#endregion  MethodEx
	} 
}