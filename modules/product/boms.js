
const raw = document.querySelector('#raw');
const unit = document.querySelector('#unit');

document.getElementById('unit')
    .addEventListener('keyup', function(event) {
        if (event.code === 'Enter')
        {
            event.preventDefault();
            document.querySelector('form').submit();
        }
    });
    
document.body.addEventListener('click', (event)=>{
    generateUnit();
});

raw.addEventListener('change', (event) => {
    generateUnit();
});

function generateUnit() {

    if (raw.value != ""){
        let text = raw.value;
        const myArray = text.split(" / ");
        let word = myArray[2];
    
        unit.value = word;
    }
  
}



