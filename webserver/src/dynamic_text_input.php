<?php 

function addDynamicTextInput($name, $list=null, $required=false){
    echo <<< LOGIC
    <div id="$name-input-container">
    </div>
    <button type="button" id="$name-add-button">إضافة</button>
    <button type="button" id="$name-delete-button">حذف</button>
    <script>
        addDynamicTextInputLogic("$name", "$list", "$required");
    </script>
    LOGIC;
}

?>

<script>

    
function addDynamicTextInputLogic(name, list, required){
    const addButton = document.getElementById(`${name}-add-button`);
    const deleteButton = document.getElementById(`${name}-delete-button`);
    const inputFieldsContainer = document.getElementById(`${name}-input-container`);
    
    if(required)createChild();

    addButton.addEventListener('click', createChild);

    // Function to delete the last input field
    deleteButton.addEventListener('click', function() {
        const lastInputField = inputFieldsContainer.lastElementChild;
        const minimumAllowed = required? 1 : 0;
        const alertText =required? 'هذه خانة إلزامية لا يمكن حذفها' : 'حذفت كل الخانات';
        if (inputFieldsContainer.childElementCount > minimumAllowed) {
            inputFieldsContainer.removeChild(lastInputField);
        } else {
            alert(alertText);
        }
    });

    function createChild(){
        const inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.name = name + '[]';
        inputField.setAttribute('list', list);
        inputField.required = required;
        inputFieldsContainer.appendChild(inputField);
    }

}



</script>
