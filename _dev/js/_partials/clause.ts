
const clause = document.querySelector<HTMLInputElement>('input[name="conditions_to_approve[terms-and-conditions]"]');
if(clause) {
    clause.addEventListener('click', () => {
        validateClause(clause);
    });
}

export const validateClause = (elm:HTMLInputElement) => null === elm || elm.checked;
