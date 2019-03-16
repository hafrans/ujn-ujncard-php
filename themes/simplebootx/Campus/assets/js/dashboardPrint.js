$('#dashboard').dashboard({draggable: true});

canPrint = false;// true 可打印 false 不可打印
printType = 0;// 1 单照片打印， 2 双照片打印
printStatus = 0;
$(document).ready(function(){
	//检测打印机器驱动
		setTimeout(function(){
			try{ 
			    var LODOP=getLodop(); 
				if (LODOP.VERSION) {
					 if (LODOP.CVERSION)
						 $("#plugin_status").html('<i class="icon-check"></i>&nbsp;&nbsp;&nbsp;当前云打印控件可用!\n 版本:'+LODOP.CVERSION+"(内含Lodop"+LODOP.VERSION+")"); 
					 else
						 $("#plugin_status").html('<i class="icon-check"></i>&nbsp;&nbsp;&nbsp;本机已成功安装了打印机控件！\n 版本号:'+LODOP.VERSION); 
					canPrint = true;
				};
			 }catch(err){ 
				 //$("#plugin_status").html("插件初始化失败！");
	 		 } 
		},3000);
	
	
	$("#btn-print").click(function(){
		if(printType == 2 && typeof($('.rd:checked').val()) == 'undefined'){
			new $.zui.Messager('请选择打印照片以便审核！', {
			    icon: 'heart',
			    type: 'danger',
			    placement: 'top-right' // 定义显示位置
			}).show();
			return false;
			
			
		}
		if(printStatus == 3 && $('.rd:checked').val() == 'r'){
			new $.zui.Messager('您选的照片审核失败，不能打印！', {
			    icon: 'heart',
			    type: 'danger',
			    placement: 'top-right' // 定义显示位置
			}).show();
			return false;
		}
		
		if(printStatus == 1){
			bootbox.confirm({ 
				  size: "small",
				  message: "<span style=\"font-size:1.5em;font-weight:bold;\">您确定要"+($('.rd:checked').val() == 'r' ? "通过":"驳回")+"该用户的审核吗？</span><br><br>注意：您的该项行为将会被系统记录", 
				  callback: function(result){ 
					  if(result){
						  $('#selectPhoto').modal('hide');
							window.previewPage = new  $.zui.ModalTrigger({
								title:"打印预览",
								width:502,
								height:435,
								position:100,
								iframe:printURI+"&position="+$(".rd:checked").val(),
							});
							window.previewPage.show();
					  }
				  }
			});
		}else{
			 $('#selectPhoto').modal('hide');
			 	window.previewPage = new  $.zui.ModalTrigger({
					title:"打印预览<等待系统弹出，不要自行关闭>",
					width:502,
					height:435,
					position:100,
					iframe:printURI+"&position="+$(".rd:checked").val(),
				});
			 	window.previewPage.show();
		}
		
		
		
		

//		$window.show();
		
		
		
		
		
	});
	
		
});
function postForPrint(obj){
	//return false;
	
	if(!obj.cardno.value.match(/^\d{11,12}$/)){
		new $.zui.Messager('校园卡号不正确！', {
		    icon: 'heart',
		    type: 'danger',
		    placement: 'top-right' // 定义显示位置
		}).show();
		return false;
	}
	
	if(!canPrint){
		new $.zui.Messager('打印系统未就绪！', {
		    icon: 'heart',
		    type: 'danger',
		    placement: 'top-right' // 定义显示位置
		}).show();
		return false;
	}
	
	printType = 0;
	printStatus = 0;
	$('#1').hide();
	$('#2').hide();
	$('#4').hide();
	$('#5').hide();
	$('#btn-print').hide();
	$('#3').show();
	$('#ll_left').removeAttr("checked");
	$('#ll_right').removeAttr("checked");
	
	$("#selectPhoto").modal({
		position:100
	});
	setTimeout(function(){
		$.ajax({
			url:postURI,
			type:"POST",
			data:"showcardno="+$("#showcardno").val(),
			success:function(_json){
				if(typeof(_json) != "object"){
					$('#4').show();
					$('#3').hide();	
				}
				
				if(_json.status){
					
					printType = _json.count;
					if(_json.count == 1){
						
						$("#single_Left").attr('src',_json.ph1);
						$("#s_xm").text(_json.name);
						$("#s_xb").text(_json.sex);
						$("#s_de").text(_json.dept);
						$("#s_bm").text(_json.depttop);
						$("#s_sf").text(_json.statusname);
						$('#btn-print').show();
						$('#1').show();
						$('#3').hide();
					}else if(_json.count == 2){
						
						$("#double_Left").attr('src',_json.ph1);
						$("#double_Right").attr('src',_json.ph2);
						$("#d_xm").text(_json.name);
						$("#d_de").text(_json.dept);
						$("#d_xb").text(_json.sex);
						$("#d_bm").text(_json.depttop);
						$("#d_sf").text(_json.statusname);
						$('#d_ti').text(_json.apply_time);
						$('#d_co').text(_json.confidence);
						$('#d_st').text(_json.apply_status == 1 ? "正等待当面审核": _json.apply_status == 2 ?"已审核成功":"审核失败" );
						printStatus = _json.apply_status;
						switch(_json.apply_status){
						case 2:
							$('#ll_right')[0].checked = true;
							break;
						case 3:
							$('#ll_left')[0].checked = true;
							break;
						}
						
						
						$('#btn-print').show();
						$('#2').show();
						$('#3').hide();
						
					}
					
					
				}else{
					$("#server-info").text("该校园卡号码不存在");
					$('#5').show();
					$('#3').hide();	
				}
				
			},
			error:function(_obj){
				$('#4').show();
				$('#3').hide();
				
			}
			
		});
	},800);
	
	
	return false;
}
