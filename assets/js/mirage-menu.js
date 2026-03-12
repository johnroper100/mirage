(function () {
    function getImmediateChildByClass(element, className) {
        for (var i = 0; i < element.children.length; i++) {
            if (element.children[i].classList.contains(className)) {
                return element.children[i];
            }
        }

        return null;
    }

    function setMenuItemExpanded(menuItem, expanded) {
        var toggle = getImmediateChildByClass(menuItem, "mirage-menu__toggle");
        var submenu = getImmediateChildByClass(menuItem, "mirage-menu__submenu");
        if (!toggle || !submenu) {
            return;
        }

        toggle.setAttribute("aria-expanded", expanded ? "true" : "false");
        submenu.hidden = !expanded;

        if (!expanded) {
            closeNestedMenus(submenu);
        }
    }

    function closeNestedMenus(container) {
        if (!container) {
            return;
        }

        var nestedMenuItems = container.querySelectorAll(".mirage-menu__item--has-children");
        nestedMenuItems.forEach(function (menuItem) {
            setMenuItemExpanded(menuItem, false);
        });
    }

    function closeSiblingMenus(menuItem) {
        if (!menuItem || !menuItem.parentElement) {
            return;
        }

        Array.prototype.forEach.call(menuItem.parentElement.children, function (siblingItem) {
            if (siblingItem !== menuItem && siblingItem.classList.contains("mirage-menu__item--has-children")) {
                setMenuItemExpanded(siblingItem, false);
            }
        });
    }

    document.addEventListener("click", function (event) {
        var toggle = event.target.closest(".mirage-menu__toggle");
        if (toggle) {
            event.preventDefault();

            var menuItem = toggle.parentElement;
            var isExpanded = toggle.getAttribute("aria-expanded") === "true";
            closeSiblingMenus(menuItem);
            setMenuItemExpanded(menuItem, !isExpanded);
            return;
        }

        document.querySelectorAll(".mirage-menu__item--has-children").forEach(function (menuItem) {
            if (!menuItem.contains(event.target)) {
                setMenuItemExpanded(menuItem, false);
            }
        });
    });

    document.addEventListener("keydown", function (event) {
        if (event.key !== "Escape") {
            return;
        }

        document.querySelectorAll(".mirage-menu__item--has-children").forEach(function (menuItem) {
            setMenuItemExpanded(menuItem, false);
        });
    });
})();
