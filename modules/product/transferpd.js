const job_order=document.querySelector('#job_order');
const url_=document.querySelector('#url');
const minute = 1000 * 60;

document.getElementById('job_order').addEventListener('keyup',function(event){
  if (event.code === 'Enter'){
      event.preventDefault();
      document.querySelector('form').submit();
  }
});

job_order.addEventListener('change',(Event) => {
  ShowJob(job_order.value);
});

function ShowJob(job){
  if (job != '') {
    // const url = 'index.php?module=product-transferpd&job='+job+'&time='+minute;
    // window.open(url, '_parent');
   document.getElementById("setup_frm").submit();
  }
}

// document.getElementById('printBarcode').addEventListener('click',function(event){
//     const id=document.getElementById('vehicle_number_detail').value
//       const url = this.getAttribute('data-url') + 'export.php?module=index-label&id='+id;
//       window.open(url, '_blank');
//     });
