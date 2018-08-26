﻿using System;
namespace SfSoft.Model
{
	/// <summary>
	/// WX_Doublenovember_Award_Detail:实体类(属性说明自动提取数据库字段的描述信息)
	/// </summary>
	[Serializable]
	public partial class WX_Doublenovember_Award_Detail
	{
		public WX_Doublenovember_Award_Detail()
		{}
		#region Model
		private int _id;
		private string _openid;
		private int _awardtype;
		private int? _award;
		private DateTime? _createdate;
		private string _remark;
        private int _origin;
		/// <summary>
		/// 
		/// </summary>
		public int ID
		{
			set{ _id=value;}
			get{return _id;}
		}
		/// <summary>
		/// 
		/// </summary>
		public string OpenId
		{
			set{ _openid=value;}
			get{return _openid;}
		}
		/// <summary>
		/// 
		/// </summary>
		public int AwardType
		{
			set{ _awardtype=value;}
			get{return _awardtype;}
		}
		/// <summary>
		/// 
		/// </summary>
		public int? Award
		{
			set{ _award=value;}
			get{return _award;}
		}
		/// <summary>
		/// 
		/// </summary>
		public DateTime? CreateDate
		{
			set{ _createdate=value;}
			get{return _createdate;}
		}
		/// <summary>
		/// 
		/// </summary>
		public string Remark
		{
			set{ _remark=value;}
			get{return _remark;}
		}
        /// <summary>
        /// 
        /// </summary>
        public int Origin
        {
            set { _origin = value; }
            get { return _origin; }
        }
		#endregion Model

	}
}
