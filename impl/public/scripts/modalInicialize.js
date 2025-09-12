import removeDeckModal from "./removeDeckModal.js";
import duplicateDeckModal from "./duplicateDeckModal.js";
import editDeckModal from "./editDeckModal.js";
import showCardModal from "./showCardModal.js";
import createCardModal from "./createCardModal.js";
import publicateDeckModal from "./publicateDeckModal.js";
import reviewDeckModal from "./reviewDeckModal.js";
import createGoalModal from "./createGoalModal.js";

document.querySelectorAll('#openModal').forEach(
    (element) => {
        let action = element.dataset.value;
        if (action === 'remove_collection')
            new removeDeckModal(element);
        if (action === 'duplicate_collection')
            new duplicateDeckModal(element);
        if (action === "edit_collection" || action === "create_collection")
            new editDeckModal(element, action);
        if (action === "show_card")
            new showCardModal(element);
        if (action === "publicate_collection")
            new publicateDeckModal(element);
        if (action === "deck_review")
            new reviewDeckModal(element);
        if (action === "create_goal")
            new createGoalModal(element);
    });
const cardBtn = document.querySelector('#openCardModal');
if (cardBtn){
    if (cardBtn.dataset.value === 'card_create')
        new createCardModal(cardBtn);
}