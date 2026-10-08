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
const downicon3 = document.getElementById("down-icon3");
const upicon3 = document.getElementById("up-icon3");
const subdir1 = document.getElementById("sub-dir1");
const subdir2 = document.getElementById("sub-dir2");
const subdir3 = document.getElementById("sub-dir3");

if(downicon1 && upicon1 && subdir1){
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
}

if(downicon2 && upicon2 && subdir2){

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
}

if(downicon3 && upicon3 && subdir3){

    downicon3.addEventListener("click", (e)=>{
        e.preventDefault();
        downicon3.style="display:none;";
        upicon3.style="display:inline-block;";
        subdir3.style="display:inline-block;";

    })

    upicon3.addEventListener("click", (e)=>{
        e.preventDefault();
        upicon3.style="display:none;";
        downicon3.style="display:inline-block;";
        subdir3.style="display:none;";

    })
}

//filter
const filterbtn = document.getElementById("filter-btn");
const filteroptions = document.getElementById("filter");
var flag2 = 0;

filterbtn.addEventListener("click", (e)=>{
    e.preventDefault();
    if(flag2==0)
    {
        filterbtn.style="display:none;";
        filteroptions.style="display:inline-block;";
        flag2 = 1;
    }
    else
    {
        filterbtn.style="display:inline-block;";
        filteroptions.style="display:none;";
        flag2 = 0;
    }
    


})

