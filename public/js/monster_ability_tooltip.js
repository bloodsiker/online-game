(function () {
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function positionTooltip(tooltip, event) {
        var viewportWidth = document.documentElement.clientWidth || window.innerWidth;
        var viewportHeight = document.documentElement.clientHeight || window.innerHeight;
        var margin = 20;
        var x = event.clientX + 10;
        var y = event.clientY + 10;
        var maxX = viewportWidth - tooltip.offsetWidth - margin;
        var maxY = viewportHeight - tooltip.offsetHeight - margin;

        if (x > maxX) {
            x = event.clientX - tooltip.offsetWidth - 10;
        }
        if (y > maxY) {
            y = event.clientY - tooltip.offsetHeight - 10;
        }

        tooltip.style.left = Math.max(7, Math.min(x, Math.max(7, maxX))) + 'px';
        tooltip.style.top = Math.max(7, Math.min(y, Math.max(7, maxY))) + 'px';
    }

    function tooltipElement(element) {
        return document.getElementById(element.dataset.tooltipContainer || 'monster_ability_alt');
    }

    function renderTooltip(element) {
        var tooltip = tooltipElement(element);
        if (!tooltip) {
            return null;
        }

        var meta = [];
        try {
            meta = JSON.parse(element.dataset.tooltipMeta || '[]');
        } catch (error) {
            meta = [];
        }

        var darkRow = false;
        var rows = meta.map(function (item) {
            var rowClass = darkRow ? ' list_dark' : '';
            darkRow = !darkRow;

            return '<tr class="skill_list' + rowClass + '"><td style="color:#955c4a;font-weight:bold;white-space:nowrap;">'
                + escapeHtml(item.label) + '</td><td style="padding:2px 4px;color:#ba0000;">'
                + escapeHtml(item.value) + '</td></tr>';
        }).join('');
        var description = escapeHtml(element.dataset.tooltipDescription || '').replace(/\n/g, '<br>');

        tooltip.innerHTML = '<table width="300" border="0" cellspacing="0" cellpadding="0" class="aa-table" style="background-color:#fbd4a4;">'
            + '<tr><td width="14" class="aa-tl"><img src="/img/icon/d.gif" width="14" height="24" alt=""><br></td><td class="aa-t aa-table-t" align="center" style="vertical-align:middle"><b style="color:#955c4a;">'
            + escapeHtml(element.dataset.tooltipName) + '</b></td><td width="14" class="aa-tr"><img src="/img/icon/d.gif" width="14" height="24" alt=""><br></td></tr>'
            + '<tr><td class="aa-l" style="padding:0;"></td><td style="padding:0;">'
            + '<table width="275" style="margin:3px;" border="0" cellspacing="0" cellpadding="0" class="aa-table-t"><tr>'
            + '<td align="center" valign="top" width="60"><img src="' + escapeHtml(element.dataset.tooltipImage) + '" width="54" height="54" alt="" style="display:block;object-fit:contain;margin:2px;border:1px solid #db9f73;"></td>'
            + '<td valign="top" style="padding:4px 2px;color:#955c4a;font-weight:bold;">' + escapeHtml(element.dataset.tooltipType) + '</td>'
            + '</tr></table>'
            + ((rows || description) ? '<table class="aa-table-t" width="100%" cellspacing="0" cellpadding="0">'
                + rows
                + (description ? '<tr class="skill_list' + (darkRow ? ' list_dark' : '') + '"><td colspan="2" style="color:#59483d;padding:4px;">' + description + '</td></tr>' : '')
                + '</table>' : '')
            + '</td><td class="aa-r" style="padding:0;"></td></tr>'
            + '<tr><td class="aa-bl"></td><td class="aa-b"></td><td class="aa-br"></td></tr></table>';

        return tooltip;
    }

    window.showSkillEffectInfo = function (element, event, show) {
        var tooltip = tooltipElement(element);
        if (!tooltip) {
            return;
        }

        if (!show) {
            tooltip.style.display = 'none';
            document.onmousemove = null;

            return;
        }

        if (show === 2) {
            tooltip = renderTooltip(element);
            if (!tooltip) {
                return;
            }
            tooltip.style.display = 'block';
            document.onmousemove = function (moveEvent) {
                positionTooltip(tooltip, moveEvent || window.event);
            };
        }

        positionTooltip(tooltip, event || window.event);
    };

    // Старое имя оставлено для карточек умений и эффектов монстров.
    window.showMonsterAbilityInfo = window.showSkillEffectInfo;
}());
