var React=require("react");
var PubSub = require("pubsub-js");
var objImgIndex=0;

var RightStrMsgTotol=require("./StudentMsgType1_list_part_right_MsgTotol.jsx");
//打卡右部分下面的点赞列表和评论列表
var Fc_MsgTotol=require("./Fc_right_MsgTotol.jsx");
var Fc_comment=require("./Fc_comment.jsx");
var Fc_bindBigImg=require("./Fc_bindBigImg.jsx");
// var Fc_bindBigImg=require("./Fc_bindBigImg.jsx");//绑定图片预览

var StudentMsgType1_list_part_right=React.createClass({
	render:function (){
		var xueYuanDaKaIndex=this.props.xueYuanDaKaIndex;
		//alert(xueYuanDaKaIndex);

		var getList1type_partleft_item=this.props.getList1type_right;

		var getListPartIndex=this.props.index;
		var hasZanBtn="";
		var hasZanImgBtn=0;
		var arrHasZan=["https://cdn2.picooc.com/web/res/fatburn/image/student/student-6.png","https://cdn2.picooc.com/web/res/fatburn/image/student/student-30.png"];
		if(getList1type_partleft_item.hasPraised){
			hasZanBtn="hasZan";
			hasZanImgBtn=1;
		}
		
		return (
			<div className="col-xs-12 col-sm-12 partRight" data-part={parseInt(getListPartIndex)}>
				<div className="row partRight-paddingleft">
					<div className="col-xs-12 col-sm-12 partRight-icon">
					<span style={{backgroundImage:'url('+publicData.cardTypeBg[getList1type_partleft_item.type]+')'}}>{publicData.cardType[getList1type_partleft_item.type]}</span>
					</div>
					<div className="col-xs-12 col-sm-12 partRight-p1" dangerouslySetInnerHTML={{__html:getList1type_partleft_item.content}}></div>
					<div className="col-xs-12 col-sm-12 partRight-img"><RightStrImg getList1type_partleft_item={getList1type_partleft_item} getListPartIndex={getListPartIndex} /></div>
					<div className="col-xs-12 col-sm-12 partRight-type1">
						<span className="partRight-type1-date">{getList1type_partleft_item.checkTime}</span>
							<RightStrDelete  getList1type_partleft_item={getList1type_partleft_item} getListPartIndex={getListPartIndex}  />
							<RightStrShare  getList1type_partleft_item={getList1type_partleft_item} getListPartIndex={getListPartIndex}  />
						<span className={"partRight-type1-zan "+hasZanBtn} data-zan_num={getList1type_partleft_item.praiseNum} data-xue-yuan-da-ka-index={xueYuanDaKaIndex} data-part_index={parseInt(getListPartIndex)} data-check_id={getList1type_partleft_item.id} data-check_role_id={getList1type_partleft_item.roleId} onClick={Fc_MsgTotol.changeZan}><img src={arrHasZan[hasZanImgBtn]} /><span className="zanNum">{getList1type_partleft_item.praiseNum}</span></span>
						<span className="partRight-type1-msg" data-check_id={getList1type_partleft_item.id} data-reply_id="0" data-reply_role_id={getList1type_partleft_item.roleId} data-xue-yuan-da-ka-index={xueYuanDaKaIndex} data-part_index={parseInt(getListPartIndex)} onClick={Fc_comment.clickAddMsg}><img src="https://cdn2.picooc.com/web/res/fatburn/image/student/student-7.png" /></span>
					</div>
					<RightStrMsgTotol  getList1type_partleft_item={getList1type_partleft_item} getListPartIndex={getListPartIndex}  />
				</div>
			</div>
		)
	}
})
// onClick={Fc_bindBigImg.bindBigImg}
//右部分图片展示
var RightStrImg=React.createClass({
	render:function (){
		var getList1type_partleft_item=this.props.getList1type_partleft_item;
		
		var getListPartIndex=this.props.getListPartIndex;
		var strImg=[];
		var objImgName='img'+objImgIndex;//图片预览名
		objImgIndex++;
		if(getList1type_partleft_item.imgs!=null){
			publicData.objImg[objImgName]=getList1type_partleft_item.imgs;//图片预览对象

			if(getList1type_partleft_item.imgs.length==0){
				return <i></i>;
			}
			else if(getList1type_partleft_item.imgs.length==4){
				
				var strImg1=[];
				var key=0;
				for(var j=0;j<2;j++){
					strImg1.push(
						<div className="col-xs-4 col-sm-4 partRight-img-li " key={key} data-obj_img={objImgName} data-obj_img_index={j} style={{height:($(window).width()-(2.5+3.75)*fontHeight)/3,backgroundImage:'url('+getList1type_partleft_item.imgs[j].url+'@500h_500w_1e)'}} onClick={Fc_bindBigImg.bindBigImg}>
						</div>
						);
					key++;
				}
				strImg.push(<div className="row" key={0}>{strImg1}</div>);
				var strImg2=[];
				for(var j=2;j<4;j++){
					strImg2.push(<div className="col-xs-4 col-sm-4 partRight-img-li " key={key} data-obj_img={objImgName} data-obj_img_index={j} style={{height:($(window).width()-(2.5+3.75)*fontHeight)/3,backgroundImage:'url('+getList1type_partleft_item.imgs[j].url+'@500h_500w_1e)'}} onClick={Fc_bindBigImg.bindBigImg}></div>);
					key++;
				}
				strImg.push(<div className="row" key={1}>{strImg2}</div>);
			}
			else if(getList1type_partleft_item.imgs.length==1){
				var key=0;
				strImg.push(<div className="col-xs-12 col-sm-12 partRight-img-li partRight-img-li2" key={key} data-obj_img={objImgName} data-obj_img_index="0" style={{height:($(window).width()-(2.5+3.75)*fontHeight)*3/4,backgroundImage:'url('+getList1type_partleft_item.imgs[0].url+'@500h_500w_1e)'}} onClick={Fc_bindBigImg.bindBigImg}></div>);
				key++;
			}
			else if(getList1type_partleft_item.imgs.length==2){
				var strImg1=[];
				var key=0;
				for(var j=0;j<getList1type_partleft_item.imgs.length;j++){
					strImg1.push(<div className="col-xs-6 col-sm-6 partRight-img-li partRight-img-li3" key={key} data-obj_img={objImgName} data-obj_img_index={j} style={{height:($(window).width()-(2.5+3.75)*fontHeight)/2,backgroundImage:'url('+getList1type_partleft_item.imgs[j].url+'@500h_500w_1e)'}} onClick={Fc_bindBigImg.bindBigImg}></div>);
					key++;
				}
				strImg.push(<div className="row"  key={0}>{strImg1}</div>);
			}
			else{
				var key=0;
				for(var j=0;j<getList1type_partleft_item.imgs.length;j++){
					strImg.push(<div className="col-xs-4 col-sm-4 partRight-img-li" key={key} data-obj_img={objImgName} data-obj_img_index={j} style={{height:($(window).width()-(2.5+3.75)*fontHeight)/3,backgroundImage:'url('+getList1type_partleft_item.imgs[j].url+'@500h_500w_1e)'}} onClick={Fc_bindBigImg.bindBigImg}></div>);
					key++;
				}
				
			}
			
			if(strImg!=[]){
				//console.log(strImg.length);
					
				return (
					<div className="row">
					{strImg}
					</div>
				);
			}
			// return <i></i>;
		}
		// return <i></i>;
	}
})
//右部分删除
var RightStrDelete=React.createClass({
	deletePart:function(event){
		event.stopPropagation();
		var deleteIndex=parseInt(event.currentTarget.getAttribute("data-part_index"));
		var deleteCheckId=event.currentTarget.getAttribute("data-check_id");
		
		$(".fixbg-main-t").html("您确定删除这条打卡吗？");
		$(".fixbg").css("display","block");
		$(".fixbg-main").css("margin-top",-$(".fixbg-main").height()/2);
		$(".fixbg-main-btn1").unbind("click").click(function(){
			$(".fixbg").css("display","none");
		});
		$(".fixbg-main-btn2").unbind("click").click(function(){
			var finalUrl=ajaxLink+"/v1/api/camp/deleteCheckIn"+window.location.search+'&checkId='+deleteCheckId;
			$.ajax({
				type:"get",
				url:finalUrl,
				dataType:"json",
				success:function(data){
					if(data.code == 200){
						PubSub.publish("deletePart",deleteIndex);
						$(".fixbg").css("display","none");
					}
				}
			})
		})
	},
	render:function (){
		
		var getList1type_partleft_item=this.props.getList1type_partleft_item;
		
		var getListPartIndex=this.props.getListPartIndex;
		if(roleId!="false" && roleId==getList1type_partleft_item.roleId ){
			return (
				<span className="partRight-type1-delete" data-part_index={parseInt(getListPartIndex)} data-check_id={getList1type_partleft_item.id} onClick={this.deletePart}>删除</span>
				);
		}
		return <i></i>;
	}
})
//右部分分享
var RightStrShare=React.createClass({
	render:function (){
		
		var getList1type_partleft_item=this.props.getList1type_partleft_item;
		
		var getListPartIndex=this.props.getListPartIndex;
		return (
			<span className="partRight-type1-share"  data-check_id={getList1type_partleft_item.id} data-check_role_id={getList1type_partleft_item.roleId} onClick={Fc_MsgTotol.shareLink}><img src="https://cdn2.picooc.com/web/res/fatburn/image/cardShare/share.png" /></span>
			);
/*if(type==3 || type==4){
									strShare='';
								}*/
		return <i></i>;
	}
})

module.exports = StudentMsgType1_list_part_right; 



