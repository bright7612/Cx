// JavaScript Document
Raphael.fn.columnChart3D = function (model) {
    var paper = this,
        rad = Math.PI / 180,
        chart = this.set();
    
    //**需要同步动画的变量**//
    var anim_tongbu,ele_tongbu;
		
	function drawCircle(x,y,rx,ry,params){
		return paper.ellipse(x,y,rx,ry).attr(params);
	}
	
	function drawRect(x,y,width,height,params){
		return paper.rect(x, y, width, height).attr(params);	
	}
	
	function drawPath(pathString,params){
		return paper.path(pathString).attr(params);
	}
	
	//圆柱阴影
	function process_shadow(model){
            var p = drawCircle((model.x+(0.5*model.rx)),(model.y-5),(model.rx),(model.ry),{fill: "r#222-rgba(0,0,0,0)" ,stroke: "none", opacity:0});
				p.animate({rx:(model.rx+(0.5*model.rx)),ry:(model.ry+5)},1000,"bounce");
				chart.push(p);
        };
	
	//圆柱柱身
	 function process_body(model){
		 	//var pstr = 左下-右下-上-左上-闭合 "M200,350A100,50,0,0,0,400,350V300A100,50,0,0,1,200,300Z"
			var pstr = "M"+(model.x-model.rx)+","+model.y    +"A"+(model.rx)+","+(model.ry)+","+0+","+0+","+0+","+(model.x+model.rx)+","+(model.y)    +"V"+ (model.y) +"A"+(model.rx)+","+(model.ry)+","+0+","+0+","+1+","+(model.x-model.rx)+","+(model.y)+"Z"
            var p = drawPath(pstr,{fill:"0-"+model.body_color_fill_1+"-"+model.body_color_fill_2+":20-"+model.body_color_fill_3,opacity: 1,stroke:"none"});
			var anim = Raphael.animation({path:("M"+(model.x-model.rx)+","+model.y    +"A"+(model.rx)+","+(model.ry)+","+0+","+0+","+0+","+(model.x+model.rx)+","+(model.y)    +"V"+ (model.y-model.body_height)+"A"+(model.rx)+","+(model.ry)+","+0+","+0+","+1+","+(model.x-model.rx)+","+(model.y-model.body_height)+"Z")},1000,"bounce");
			p.animateWith(ele_tongbu,anim_tongbu,anim);
            chart.push(p);
            
        };

	//圆柱柱顶
     function process_top(model){
            var p = drawCircle(model.x,model.y,model.rx,model.ry,{fill:"30-"+model.top_color_fill_1+"-"+model.top_color_fill_2 , stroke: model.top_color_border });
            	ele_tongbu = p;
				anim_tongbu = Raphael.animation({cy:(model.y-model.body_height)},1000,"bounce");
				p.animate(anim_tongbu);
				txt = paper.text(model.x, model.y, model.text).attr({fill: "#333", stroke: "none", opacity: 1, "font-size": 12 , "font-weight":"bold" });
				txt.animate({y:(model.y-model.body_height-10)},1000,"bounce");
			 chart.push(txt);
			 chart.push(p);
        };
		
        if(model.body_height >= 20){
        	process_shadow(model);
        }
	    	process_top(model);
	    	process_body(model);
    return chart;
};

/**
 * @param _width svg图整体宽度	推荐值：40,80,100,120...
 * @param _height svg图整体高度 推荐值：100,200,300...
 * @param _values 柱状体需表示的所有值，用来分配柱状高度份额
 * @param _value 柱状体当前需要表示的值
 * @param color 柱状体用色方案，可选：red , green , blue , yellow , random , hot
 * @param _text 柱状体当前需要显示的文字
 * @return thisModel
 */
function auto3DColumnModel(_width,_height,_values,_value,_color,_text){
	//原理：根据设置的svg图的宽高最与传入的值域(_values)匹配，得出每个值匹配到的柱状体高度
	//_max_width,_max_height取值请采用10的整数倍
	//默认值配置
	var def_max_width = 40;
	var def_max_height =  200;
	var color1,color2,color3,color4,color5,color6 = "";
	def_max_width = _width;
	def_max_height = _height;
	//选择配色方案
	var colorModel = getColor(_color,Math.max.apply(null, _values),_value);
	var maxapply = Math.max.apply(null, _values)==0 ? 1 : Math.max.apply(null, _values);
	var thisModel = {
		svg_width: def_max_width,
		//svg总高度为半个底园+柱体高度+半个顶园+字体高度
		//svg_height: def_max_height,
		svg_height: (0.25 * def_max_width) + parseInt( (def_max_height - (0.25 * def_max_width) - 10 ) / maxapply * _value ) + 10,
		//圆柱椭圆的长半径 *2 + 圆柱右侧留白 +圆柱左侧的留白  = _max_width
		rx: 0.25 * def_max_width,
		//圆柱椭圆的短半径，短半径等于长半径的一半
		ry: 0.125 * def_max_width,
		//圆柱下底圆心基点x轴
		x: 0.5 * def_max_width,
		//圆柱左下角基点y轴,svg高度减去椭圆的短半径
		y: ( (0.25 * def_max_width) + parseInt( (def_max_height - (0.25 * def_max_width) - 10 ) / maxapply * _value ) + 10) - (0.125 * def_max_width),
		//圆柱的高度，最大高度为svg图最大高度-椭圆的高度（短直径）- 文本高度
		body_height: parseInt( (def_max_height - (0.25 * def_max_width) - 10 ) / maxapply * _value ),
		//配色方案
		body_color_fill_1: colorModel.c1,
		body_color_fill_2: colorModel.c2,
		body_color_fill_3: colorModel.c3,
		top_color_fill_1: colorModel.c4,
		top_color_fill_2: colorModel.c5,
		top_color_border: colorModel.c6,
		//柱状体显示的文字
		text: _text 
	}
	return thisModel;
};

/**
 * @param pram	选色模式
 * @param maxValue	柱状体表示的最大值
 * @param value	柱状体当前代表的最大值
 * @return
 */
function getColor(pram,maxValue,value){
	var color1,color2,color3,color4,color5,color6 = "";
	function pickColor(pram){
		if(pram == "blue"){
			color1 = "#185fd5";
			color2 = "#a0d3ff";
			color3 = "#185fd5";
			color4 = "#65b6fa";
			color5 = "#0b57d6";
			color6 = "rgb(105, 167, 247)";
			
			/*color1 = "#3ba5f1";
			color2 = "#5edfff";
			color3 = "#37d3f9";
			color4 = "#38d0f9";
			color5 = "#2f9fc7";
			color6 = "#93d6ff";*/
		}else if (pram == "red"){
			color1 = "#e00b0b";
			color2 = "#ffa2a2";
			color3 = "#cc0000";
			color4 = "#f65a5a";
			color5 = "#e81919";
			color6 = "#f99999";
			
			/*color1 = "#cd0333";
			color2 = "#e53629";
			color3 = "#d5271c";
			color4 = "#f54439";
			color5 = "#d3291f";
			color6 = "#df362c";*/
		}else if (pram == "yellow"){
			color1 = "#e68512";
			color2 = "#ffdba2";
			color3 = "#d6700b";
			color4 = "#f69d1d";
			color5 = "#d6700b";
			color6 = "#fcd33b";
			
			/*color1 = "#ff7300";
			color2 = "#fdaf28";
			color3 = "#ffa200";
			color4 = "#ff9902";
			color5 = "#ffa600";
			color6 = "#feba80";*/
		}else if (pram == "green"){
			color1 = "#68b909";
			color2 = "#c7f369";
			color3 = "#60aa00";
			color4 = "#9beb45";
			color5 = "#60aa00";
			color6 = "#adf65e";
			
			/*color1 = "#5d8701";
			color2 = "#a6cd1b";
			color3 = "#92bb00";
			color4 = "#82ad00";
			color5 = "#8eb800";
			color6 = "#92bd0c";*/
		}
	}
	
	if(pram == "blue"){
		pickColor("blue");
	}else if (pram == "red"){
		pickColor("red");
	}else if (pram == "yellow"){
		pickColor("yellow");
	}else if (pram == "green"){
		pickColor("green");
	}else if (pram == "random"){
		var colorRange = ["red","yellow","blue","green"];
		var random_pram = colorRange[parseInt( (Math.random()*4) )];
		pickColor(random_pram);
	}else if (pram == "hot"){
		//从低到高排色值
		var colorRange = ["blue","green","yellow","yellow"];
		var hot_pram = getHotColor(colorRange,maxValue,value);
		pickColor(hot_pram);
	}
	
	//不同颜色代表不同区间的取色方法,暂时只支持4色，蓝绿黄红
	function getHotColor(colorRange,maxValue,value){
		var dsize = colorRange.length;
		if(value>=0 && value<=(maxValue/dsize)){
			return colorRange[0];
		}else if(value>(maxValue/dsize) && value<=((maxValue/dsize)*2)){
			return colorRange[1];
		}else if(value>((maxValue/dsize)*2) && value<=((maxValue/dsize)*3)){
			return colorRange[2];
		}else if(value>((maxValue/dsize)*3) && value<=maxValue){
			return colorRange[3];
		}else{
			return "取值区间错误，无法取得合适配色";
		}
	}
	
	
	var colorModel = {
			c1: color1,
			c2: color2,
			c3: color3,
			c4: color4,
			c5: color5,
			c6: color6
	}
	
	return colorModel;
}

