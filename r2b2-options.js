(function (win, doc) {
    let cAdminPage, cForm, cPlacementTable, cPlacementTableBody, cPopupAddPlacements, cInputList, cInputListDemo,
        cInputListDelivery;


    if (doc.readyState === "complete" || doc.readyState === "interactive") {
        init();
    } else {
        doc.addEventListener('DOMContentLoaded', init);
    }

    function init() {
        cAdminPage = doc.querySelector('.r2b2-options');
        cPlacementTable = doc.querySelector('#r2b2-placement-table');
        cInputList = doc.querySelector('#r2b2_settings_list');
        cInputListDemo = doc.querySelector('#r2b2_settings_list_demo');
        cInputListDelivery = doc.querySelector('#r2b2_settings_list_delivery');

        if (!isElement(cPlacementTable) ||
            !isElement(cInputList) ||
            !isElement(cInputListDemo) ||
            !isElement(cInputListDelivery)) {
            setTimeout(init, 3000);
            console.warn('[R2B2] Essential elements not found. Next try in 3 seconds. Contact R2B2 support in case of problems.');
            return;
        }

        displayPlacementTable();

        win.r2b2 = {
            displayPopupAddPlacements
        }
    }

    /*  Display */
    function displayPlacementTable() {
        if (!cPlacementTable) throw Error('Table not found');
        cPlacementTableBody = cPlacementTable.querySelector('tbody');
        if (cPlacementTableBody) {
            cPlacementTableBody.remove();
        }

        cPlacementTableBody = doc.createElement('tbody');
        cPlacementTable.appendChild(cPlacementTableBody);

        const placements = getPlacementsFromList(cInputList);
        if (placements.length === 0) {
            for (let i = 0; i < 3; i++) {
                let tr = doc.createElement('tr');
                let td1 = doc.createElement('td');
                let td2 = doc.createElement('td');
                let td3 = doc.createElement('td');
                let td4 = doc.createElement('td');
                tr.appendChild(td1);
                tr.appendChild(td2);
                tr.appendChild(td3);
                tr.appendChild(td4);
                cPlacementTableBody.appendChild(tr);
            }
        } else {
            for (const placement of placements) {
                let tr = doc.createElement('tr');
                let tdName = doc.createElement('td');
                let tdType = doc.createElement('td');
                let tdState = doc.createElement('td');
                let tdDemo = doc.createElement('td');

                tdName.textContent = getAYMPlacementName(placement);
                if (placement.indexOf('sticky') !== -1 || placement.indexOf('fixed') !== -1) {
                    tdType.textContent = 'Sticky';
                } else if (placement.indexOf('vignette') !== -1) {
                    tdType.textContent = 'Vignette';
                } else {
                    tdType.textContent = '-';
                }
                const plNameClean = placement.replaceAll(/[ \.\/\-]/gi, '_');
                tdState.appendChild(createToggle('delivery-' + plNameClean, 'delivery-' + plNameClean, isPlacementInList(placement, cInputListDelivery), createCheckboxListenerDelivery(placement)));
                tdDemo.appendChild(createToggle('demo-' + plNameClean, 'demo-' + plNameClean, isPlacementInList(placement, cInputListDemo), createCheckboxListenerDemo(placement)));

                tr.appendChild(tdName);
                tr.appendChild(tdType);
                tr.appendChild(tdState);
                tr.appendChild(tdDemo);
                cPlacementTableBody.appendChild(tr);
            }
        }
    }

    function displayPopupAddPlacements() {
        // if (cPopupAddPlacements) throw new Error('Popup already rendered');
        if (cPopupAddPlacements) closePopupAddPlacements();

        cPopupAddPlacements = doc.createElement('div');
        const cBack = doc.createElement('div');
        const cFront = doc.createElement('div');
        const cCloser = doc.createElement('button');
        const cHeadline = doc.createElement('h1');
        const cText1 = doc.createElement('p');
        const cText2 = doc.createElement('p');
        const cTextArea = doc.createElement('textarea');
        const cSave = doc.createElement('button');

        cPopupAddPlacements.className = 'r2b2-popup';
        cFront.className = 'r2b2-popup-foreground';
        cBack.className = 'r2b2-popup-background';
        cBack.addEventListener('click', closePopupAddPlacements);

        cCloser.className = 'r2b2-popup-closer';
        cCloser.textContent = '⨯';
        cCloser.title = 'Close popup';
        cCloser.addEventListener('click', closePopupAddPlacements);
        cCloser.focus();

        cHeadline.textContent = 'Add placements';
        cText1.innerHTML = 'Insert the source codes, which you have acquired from R2B2, in this format:' + '<br><code>&lt;script type="text/javascript" src="//delivery.r2b2.io/get/example.com/generic/sticky"&gt;&lt;/script&gt;</code>';
        cText2.textContent = 'Or copy and paste all the content from the source code file which you have acquired in the R2B2 e-mail.';

        cTextArea.style.width = '100%';
        cTextArea.setAttribute('rows', '8');

        cSave.className = 'button button__primary button--sm';
        cSave.innerHTML = '<span class="text-block text-block--bold text-block--md">Add</span>';
        cSave.addEventListener('click', () => processPopupAddPlacements(cTextArea.value));


        cFront.appendChild(cCloser);
        cFront.appendChild(cHeadline);
        cFront.appendChild(cText1);
        cFront.appendChild(cText2);
        cFront.appendChild(cTextArea);
        cFront.appendChild(cSave);
        cPopupAddPlacements.appendChild(cFront);
        cPopupAddPlacements.appendChild(cBack);
        cAdminPage.appendChild(cPopupAddPlacements);
        cTextArea.focus();
    }

    function closePopupAddPlacements() {
        if (typeof cPopupAddPlacements === "object" && typeof cPopupAddPlacements.remove === "function") {
            cPopupAddPlacements.remove();
            cPopupAddPlacements = null;
        }
        displayPlacementTable();
    }


    function createToggle(id, name, checked, eventListener) {
        const label = doc.createElement('label');
        const input = doc.createElement('input');
        const swField = doc.createElement('span');
        const swLabel = doc.createElement('span');

        label.className = 'switch';
        input.name = name;
        input.id = id;
        input.type = 'checkbox';
        input.checked = checked;
        input.addEventListener('change', eventListener);
        swField.className = 'switch__field';
        swLabel.className = 'switch__label';
        label.appendChild(input);
        label.appendChild(swField);
        label.appendChild(swLabel);
        return label;
    }

    function createCheckboxListenerDelivery(placement) {
        return function () {
            if (this.checked) {
                addPlacementToList(placement, cInputListDelivery);
            } else {
                removePlacementFromList(placement, cInputListDelivery);
            }
        }
    }

    function createCheckboxListenerDemo(placement) {
        return function () {
            if (this.checked) {
                addPlacementToList(placement, cInputListDemo);
            } else {
                removePlacementFromList(placement, cInputListDemo);
            }
        }
    }

    /*  Processing  */
    function processPopupAddPlacements(newPlacements) {
        if (typeof newPlacements !== "string") throw new Error('Invalid data in new placements');
        const regexScript = /(src=["'`]([a-z0-9\.\-_/]+)["'`])/gim;
        const regexUrl = /\/\/(delivery.r2b2.io|delivery.r2b2.cz|trackad.cz|track.us.org|adtrack.docker)\/get\/([a-z0-9\._\-]+)\/([a-z0-9\._]+)\/([a-z0-9\._]+)\/?([a-z0-9\._]*)/i;
        for (const match of newPlacements.matchAll(regexScript)) {
            const srcUrl = match[2];
            const urlParts = srcUrl.match(regexUrl);
            let placement = [
                urlParts[2],
                urlParts[3],
                urlParts[4]
            ].join('/');
            if (urlParts[5] && !['desktop', 'classic', '0'].includes(urlParts[5])) {
                placement += '/mobile';
            }
            if (addPlacementToList(placement, cInputList)) {
                addPlacementToList(placement, cInputListDelivery);
            }
        }

        closePopupAddPlacements();
    }

    function getPlacementsFromList(listElement) {
        if (!isElement(listElement)) throw new Error('List is not an Element');
        const value = listElement.value;
        if (value === '') {
            return [];
        } else {
            return value.split('\n');
        }
    }

    /**
     *
     * @param placement
     * @param listElement
     * @returns {boolean}   True = Placement added.
     *                      False = Placement not added.
     */
    function addPlacementToList(placement, listElement) {
        if (!isElement(listElement)) throw new Error('List is not an Element');
        if (isPlacementInList(placement, listElement)) return false;
        let placements = getPlacementsFromList(listElement);
        placements.push(placement);
        listElement.value = placements.join('\n');
        warnBeforeExit();
        return true;
    }

    function removePlacementFromList(placement, listElement) {
        if (!isElement(listElement)) throw new Error('List is not an Element');
        let placements = getPlacementsFromList(listElement);
        for (var i = 0; i < placements.length; i++) {
            if (placement === placements[i]) {
                placements.splice(i, 1);
            }
        }
        listElement.value = placements.join('\n');
        warnBeforeExit();
    }

    function isPlacementInList(placement, listElement) {
        if (!isElement(listElement)) throw new Error('List is not an Element');
        const placements = getPlacementsFromList(listElement);
        for (var i = 0; i < placements.length; i++) {
            if (placement === placements[i]) {
                return true;
            }
        }
        return false;
    }

    function isElement(element) {
        return element instanceof Element || element instanceof HTMLDocument;
    }

    function getAYMPlacementName(name) {
        let parts = name.split('/');
        if (parts.length === 3 || parts.length === 4) {
            const d = parts[0];
            const g = parts[1];
            const p = parts[2];
            const m = parts[3];

            return `${g} ${p} ${m === 'mobile' ? 'mobile ' : ''}(${d})`;
        }
        doError('Unknown name format');
        return '?';
    }

    function warnBeforeExit() {
        const listener = function (e) {
            e.preventDefault();
            e.returnValue = '';

            var confirmationMessage = 'You have unsaved changes. Are you sure you want to leave this page?';
            (e || window.event).returnValue = confirmationMessage;
            return confirmationMessage;
        }
        const submitBtn = window.document.querySelector('.r2b2-options input[type=submit]');
        if (submitBtn) {
            submitBtn.addEventListener('click', function () {
                window.removeEventListener('beforeunload', listener);
            })
        }
        window.addEventListener('beforeunload', listener);
    }

    function doError(message) {
        console.error('[R2B2]', message);
    }

})(window, window.document)
