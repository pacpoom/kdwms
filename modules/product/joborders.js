const model_no = document.querySelector('#model');
const unit = document.querySelector('#unit');

document.getElementById('unit').addEventListener('keyup',function(event){
    if (event.code === 'Enter'){
        event.preventDefault();
        document.querySelector('form').submit();
    }
});

document.body.addEventListener('click',(Event) => {
    getUnit();
});

model_no.addEventListener('change',(Event) =>{
    getUnit();
});

function getUnit() {
    if (model_no.value != "") {
        let text = model_no.value;
        const myArray = text.split(" / ");
        unit.value = myArray[2];
    }
}