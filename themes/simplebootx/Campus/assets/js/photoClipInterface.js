

function photoClipInterfaceInit(
	photoClipInterface,
	photoClipOkBtn,
	headPortraitInterface,
	headPortraitWrapper)
{
//	alert("photoClipInterfaceInit");

	$(headPortraitWrapper).click(function()
	{
//		alert("click");
		$('#file').click();//显示头像修改菜单（相机和文档），File控件在标准浏览器中是不允许通过代码进行触发的，只能手动点击触发。
	});
	
  //topMenu
	var objTopMenu = new TopMenu("topMenu-wrapper2", 
	{
		backgroundColor: "#FFFFFF",//背景颜色
		fontColor: "#1AAD19",//字体颜色
		
//		name: "活动",//中间名称
//		nameSize: "1rem",//字体尺寸，范围=1rem~2rem
//		nameWeight: "bold",//字体加粗，normal、bold
		
//		leftName: "上一步",//左侧名称
//		leftNameSize: "1.4em",//字体尺寸，范围=1rem~2rem
//		leftNameWeight: "normal",//字体加粗，normal、bold

		height:"1.25em",
		
		rightName: "确定",//右侧名称
		rightNameSize: "1rem",//字体尺寸，范围=1rem~2rem
		rightNameWeight: "normal",//字体加粗，normal、bold
		
//		leftIcon: "../img/back-white.png",//左侧图标

//		rightIcon: "img/userMale-white.png",//右侧图标
		
		callbackClickLeft: function()
		{//单击左侧名称、左侧图标
//			alert("callbackClickLeft");

			$(headPortraitInterface).show();
			$(photoClipInterface).hide();
		},
		
		callbackClickRight: function()
		{//单击右侧名称、右侧图标
//			alert("callbackClickRight");
		}
	});

	var body_width = $('body').width();
    var body_height = $('body').height();
	
	var objPhotoClip = $("#clipArea").photoClip(
	{
		width: 263,
		height:351,
		file: "#file",
		view: "#hit",
		ok: photoClipOkBtn,//截图确认按钮
		loadStart: function () 
		{


			$(photoClipInterface).show();
			$(headPortraitInterface).hide();
			
			$('.lazy_tip span').text('');
			$('.lazy_cover,.lazy_tip').show();
		},
		loadError: function () 
		{

			alert("图片加载失败");
		},
		loadComplete: function () 
		{


			$('.lazy_cover,.lazy_tip').hide();
		},
		clipStart: function () 
		{


			$('.lazy_cover,.lazy_tip').show();
		},
		clipNoPhoto: function () 
		{

			alert("亲，当前没有图片可以裁剪!");
			$('.lazy_cover,.lazy_tip').hide();
		},
		clipFinish: function (dataURL) 
		{
			upload = true;
			$(headPortraitWrapper+" img").attr('src',dataURL);
			
			$(headPortraitInterface).show();
			$(photoClipInterface).hide();
			
			$('.lazy_cover,.lazy_tip').hide();
		}
	});
	
}
