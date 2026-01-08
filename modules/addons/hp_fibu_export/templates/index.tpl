<div>
    <form method="post">
        <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
            <tbody>
            <tr>
                <td width="15%" class="fieldlabel">
                    Date select
                </td>
                <td class="fieldarea">
                    <div class="form-group date-picker-prepend-icon">
                        <label for="inputInvoiceDate" class="field-icon">
                            <i class="fal fa-calendar-alt"></i>
                        </label>
                        <input id="inputInvoiceDate" type="text" name="date"
                               class="form-control date-picker-search" data-opens="left" value="{$defaultStart} - {$defaultEnd}">
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="btn-container">
            <input type="submit" value="create archive" class="btn btn-default">
        </div>
    </form>
</div>