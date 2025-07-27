
function manageUIVisibility(userType) {
    switch(userType) {
        case 2:
            showVendElements();
            break;
        default:
            showAllElements();
            break;
    }
}
function showAllElements() {}function showVendElements() {
    var element = $("label[for='Vendedor-filter']").closest('.col-md-2'); 
    console.log("Elemento para ocultar:", element);      if (element.length) {
        element.hide();  
        
    } 
    $('#bt_update_visit').hide();
}
