//side-bar
//side-bar
const sideicon = document.getElementById("side-icon");
const sidebar = document.getElementById("side-bar");
const main = document.getElementById("main");
var flag = 0;

sideicon.addEventListener("click", (e)=>{
    e.preventDefault();
    if(flag==0)
    {
        sidebar.style="display:inline-block;";
        main.style="width: 80vw; margin-left:20%";
        flag = 1;
    }
    else
    {
        sidebar.style="display:none;";
        main.style="width:100vw; margin-left:0%;";
        flag = 0;
    }
    

})

//sub-dir
const downicon1 = document.getElementById("down-icon1");
const upicon1 = document.getElementById("up-icon1");
const downicon2 = document.getElementById("down-icon2");
const upicon2 = document.getElementById("up-icon2");
const subdir1 = document.getElementById("sub-dir1");
const subdir2 = document.getElementById("sub-dir2");

downicon1.addEventListener("click", (e)=>{
    e.preventDefault();
    downicon1.style="display:none;";
    upicon1.style="display:inline-block;";
    subdir1.style="display:inline-block;";

})

upicon1.addEventListener("click", (e)=>{
    e.preventDefault();
    upicon1.style="display:none;";
    downicon1.style="display:inline-block;";
    subdir1.style="display:none;";

})

downicon2.addEventListener("click", (e)=>{
    e.preventDefault();
    downicon2.style="display:none;";
    upicon2.style="display:inline-block;";
    subdir2.style="display:inline-block;";

})

upicon2.addEventListener("click", (e)=>{
    e.preventDefault();
    upicon2.style="display:none;";
    downicon2.style="display:inline-block;";
    subdir2.style="display:none;";

})
