
function initComplexArea(a, k, h, p, q, sub_arr, d, b, l) {
    var f = initComplexArea.arguments;// console.log(f)
    var m = document.getElementById(a);
    var o = document.getElementById(k);
    var n = document.getElementById(h);
    var e = 0;
    var c = 0;// 避免 e 空
    if (p != undefined) {
        if (d != undefined) { d = parseInt(d); } else { d = 0; }
        if (b != undefined) { b = parseInt(b); } else { b = 0; }
        if (l != undefined) { l = parseInt(l); } else { l = 0 }
        // 获取省
        n[0] = new Option("请选择 ", 0);
        for (e = 0; e < p.length; e++) {
            if (p[e] == undefined) { continue; }
            if (f[6]) {
                if (f[6] == true) {
                    if (e == 0) { continue; }
                }
            }
            m[c] = new Option(p[e], e);
            if (d == e) { m[c].selected = true; }
            c++
        }
        // 获取市
        if (q[d] != undefined) {
            c = 0;
            for (e = 0; e < q[d].length; e++) {
                if (q[d][e] == undefined) { continue; }
                if (f[7]) {
                    if (f[7] == true) {
                        if ((e % 100) == 0) { continue; }
                    }
                }
                // o[c] = new Option("请选择 ", 0);
                o[c] = new Option(q[d][e], e);
                if (b == e) { o[c].selected = true; }
                c++
            }
        }
        // 获取区
        if (sub_arr[b] != undefined) {
            // console.log(sub_arr[1301]);
            // console.log(sub_arr[b]);
            c = 0; 
            for (e = 0; e < sub_arr[b].length; e++) {
                if (sub_arr[b][e] == undefined) { continue }
                if (f[8]) {
                    if (f[8] == true) {
                        if ((e % 100) == 0) { continue }
                    }
                }
                n[c] = new Option(sub_arr[b][e], e);
                // console.log(c)
                // console.log(n[c])
                if (l == e) { n[c].selected = true }
                c++
            }
        }
    }
}

function changeComplexProvince(f, k, e, d) {
    var c = changeComplexProvince.arguments; var h = document.getElementById(e);
    var g = document.getElementById(d); var b = 0; var a = 0; removeOptions(h); f = parseInt(f);
    if (k[f] != undefined) {
        for (b = 0; b < k[f].length; b++) {
            if (k[f][b] == undefined) { continue }
            if (c[3]) { if ((c[3] == true) && (f != 71) && (f != 81) && (f != 82)) { if ((b % 100) == 0) { continue } } }
            h[a] = new Option(k[f][b], b); a++
        }
    }
    removeOptions(g); g[0] = new Option("请选择 ", 0);
    if (f == 11 || f == 12 || f == 31 || f == 71 || f == 50 || f == 81 || f == 82) {
        if ($("#" + d + "_div"))
        { $("#" + d + "_div").hide(); }
    }
    else {
        if ($("#" + d + "_div")) { $("#" + d + "_div").show(); }
    }
}

function changeCity(c, a, t) {
    $("#" + a).html('<option value="0" >请选择</option>');
    $("#" + a).unbind("change");
    c = parseInt(c); 
    var _d = sub_arr[c];
    var str = "";     
    str += "<option value='0' >请选择</option>";
    for (var i = c * 100; i < _d.length; i++) {
        if (_d[i] == undefined) continue; 
        str += "<option value='" + i + "' >" + _d[i] + "</option>";
    }
    $("#" + a).html(str);
    
}

function removeOptions(c) {
    if ((c != undefined) && (c.options != undefined)) {
        var a = c.options.length;
        for (var b = 0; b < a; b++) {
            c.options[0] = null;
        }
    }
}




//得到地区码
function getAreaID(){
    var areaID = 0;          
    if($("#seachdistrict").val() != "0"){
        areaID = $("#seachdistrict").val();                
    }else if ($("#seachcity").val() != "0"){
        areaID = $("#seachcity").val();
    }else{
        areaID = $("#seachprov").val();
    }
    return areaID;
}
//根据地区码查询地区名
function getAreaNamebyID(areaID){
    var areaName = "";
    if(areaID.length == 2){
        areaName = area_array[areaID];
    }else if(areaID.length == 4){
        var index1 = areaID.substring(0, 2);
        areaName = area_array[index1] + " " + sub_array[index1][areaID];
    }else if(areaID.length == 6){
        var index1 = areaID.substring(0, 2);
        var index2 = areaID.substring(0, 4);
        areaName = area_array[index1] + " " + sub_array[index1][index2] + " " + sub_arr[index2][areaID];
    }
    return areaName;
}
// 显示地区码
function showAreaID() {
    //地区码
    var areaID = getAreaID();
    //地区名
    var areaName = getAreaNamebyID(areaID);
    alert("您选择的区域码：" + areaID + " ，区域名：" + areaName);            
}
