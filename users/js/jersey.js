  (function () {
    const jerseyType = document.getElementById('jerseyType');
    const searchInput = document.getElementById('searchInput');

    // store the last selected option text (or empty)
    let lastSelectedText = '';

    // when dropdown changes, populate the input and remember text
    jerseyType.addEventListener('change', function () {
      const selText = jerseyType.options[jerseyType.selectedIndex].text.trim();

      if (jerseyType.value === '') {
        // reset selection => clear input
        lastSelectedText = '';
        searchInput.value = '';
        searchInput.placeholder = 'Search jersey name...';
      } else {
        // put the selected text into the input (keeps caret at end)
        lastSelectedText = selText;
        // If input already contains other user text, we put prefix + user text separated by space
        // But simpler: set input to selectedText and focus — user can continue typing after it.
        searchInput.value = selText + (searchInput.value ? ' ' : '');
        searchInput.focus();
      }
    });

    // when user types or removes text, decide whether to reset the dropdown
    searchInput.addEventListener('input', function () {
      const value = searchInput.value.trim();

      // case 1: input cleared -> reset select
      if (value === '') {
        jerseyType.value = '';
        lastSelectedText = '';
        return;
      }

      // case 2: user removed the last-selected text from input -> reset select
      if (lastSelectedText && value.indexOf(lastSelectedText) === -1) {
        jerseyType.value = '';
        lastSelectedText = '';
      }
      // else keep the select as-is (user may be editing after the selected text)
    });

    // Optional: before form submit, if input still equals selected text only, that's fine.
    // No additional handling required; the form will send type and query.
  })();