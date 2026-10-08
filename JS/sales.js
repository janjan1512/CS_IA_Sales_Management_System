//side-bar

const sideicon = document.getElementById("side-icon");
const sidebar = document.getElementById("side-bar");
const main = document.getElementById("main");
const button = document.getElementById("buttons");
var flag = 0;

sideicon.addEventListener("click", (e)=>{
    e.preventDefault();
    if(flag==0)
    {
        sidebar.style="display:inline-block;";
        main.style="width: 80vw; margin-left:20%";
        button.style="flex-direction:column; margin-left:20%; gap:10px;";
        flag = 1;
    }
    else
    {
        sidebar.style="display:none;";
        main.style="width:100vw; margin-left:0%;";
        button.style="flex-direction:row; margin-left:0%;";
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


//filter


const filterbtn = document.getElementById("filter-btn");
const filteroptions = document.getElementById("filter");
var flag2 = 0;

if(filterbtn){
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

}




//pdf
document.addEventListener('DOMContentLoaded', function() {
    const pdfButton = document.getElementById('generate-pdf');
    if (!pdfButton) {
        console.log('PDF button not found!');
        return;
    }
    
    console.log('PDF button found, attaching event listener...');
    pdfButton.addEventListener('click', async function() {
        console.log('PDF button clicked!');
        
        try {
            if (!window.jspdf) {
                alert('jsPDF library not loaded!');
                return;
            }

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });

            // table
            const tableElement = document.getElementById('table-container');
            if (tableElement) {
                console.log('Table found, generating PDF...');
                pdf.setFillColor(211, 248, 216); 
                pdf.rect(0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight(), 'F');
                pdf.setFont('times', 'bold'); 
                pdf.setFontSize(18);
                pdf.text('Customer Orders Table', 15, 20);
            
                await new Promise(resolve => setTimeout(resolve, 300));
                
                const tableCanvas = await html2canvas(tableElement, {
                    scale: 3,
                    useCORS: true,
                    allowTaint: true
                });
                const imgProps = pdf.getImageProperties(tableCanvas.toDataURL('image/png'));
                const pdfWidth = pdf.internal.pageSize.getWidth() - 40;
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                const xPos = (pdf.internal.pageSize.getWidth() - pdfWidth) / 2;
                pdf.addImage(tableCanvas.toDataURL('image/png'), 'PNG', xPos, 40, pdfWidth, pdfHeight);
            }

            // chart
            const chartElement = document.getElementById('chart-container');
            if (chartElement) {
                console.log('Chart container found, adding to PDF...');
                if (tableElement) pdf.addPage();
                pdf.setFillColor(211, 248, 216); 
                pdf.rect(0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight(), 'F');
                pdf.setFont('courier', 'bold'); 
                pdf.setFontSize(18);
                pdf.text('Sales Trends Chart', 15, 20);
                
                try {
                    console.log('Waiting for chart to render...');
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    console.log('Capturing chart with html2canvas...');
                    const chartCanvas = await html2canvas(chartElement, {
                        backgroundColor: null,
                        scale: 3,
                        logging: false,
                        useCORS: true,
                        allowTaint: true
                    });
                    
                    console.log('Chart captured successfully, size:', chartCanvas.width, 'x', chartCanvas.height);
                    const imgData = chartCanvas.toDataURL('image/png');
                    
                    if (imgData && imgData !== 'data:,') {
                        const imgProps = pdf.getImageProperties(imgData);
                        const pdfWidth = pdf.internal.pageSize.getWidth() - 40; // More margin
                        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                        const maxHeight = pdf.internal.pageSize.getHeight() - 60;
                        const finalHeight = Math.min(pdfHeight, maxHeight);   
                        const xPos = (pdf.internal.pageSize.getWidth() - pdfWidth) / 2;
                        pdf.addImage(imgData, 'PNG', xPos, 40, pdfWidth, finalHeight);
                        console.log('Chart added to PDF successfully');
                    } else {
                        throw new Error('Chart capture returned empty data');
                    }
                } catch (chartError) {
                    console.error('Error capturing chart:', chartError);

                    pdf.setFontSize(12);
                    pdf.text('Chart could not be captured.', 15, 50);
                    pdf.text('Error: ' + chartError.message, 15, 65);
                }
            }

            if (!tableElement && !chartElement) {
                alert('No table or chart found to export!');
                return;
            }

            console.log('Saving PDF...');
            pdf.save('sales-trends-chart.pdf');
            console.log('PDF should be downloaded now');
            
        } catch (error) {
            console.error('PDF generation failed:', error);
            alert('Failed to generate PDF: ' + error.message);
        }
    });
});