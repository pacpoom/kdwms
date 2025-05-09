
const zone = document.querySelector('#zone');
const area = document.querySelector('#area');
const bin = document.querySelector('#bin');
const location_code = document.querySelector('#location_code');

document.getElementById('location_code')
    .addEventListener('keyup', function(event) {
        if (event.code === 'Enter')
        {
            event.preventDefault();
            document.querySelector('form').submit();
        }
    });
    
document.body.addEventListener('click', (event)=>{
    generateLocationCode();
});

zone.addEventListener('change', (event) => {
    generateLocationCode();
});
area.addEventListener('change', (event) => {
    generateLocationCode();
});
bin.addEventListener('change', (event) => {
    generateLocationCode();
});

function generateLocationCode() {
    if ((zone.value !='') && (area.value !='') && (bin.value !='')) {
        location_code.value = zone.value +"-"+ area.value +"-"+ bin.value;
    } else {
        location_code.value ="";
    }
    
}



