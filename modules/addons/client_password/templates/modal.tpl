<style>
    .input-group.tab-styling input {
        padding: 20px;
    }
    .input-group.tab-styling {
        box-shadow: rgba(0, 0, 0, 0.1) 0px 0px 0px 1px;
        display: flex;
        margin-bottom: 10px;
    }
    .input-group.tab-styling .tab-styling-btns {
        position: absolute;
        z-index: 9;
        top: 0;
        right: 0;
        bottom: 0;
        height: 100%;
        display: flex;
    }
    .input-group.tab-styling input {
        box-shadow: none;
    }
    .tab-styling-btns a, .tab-styling-btns button {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        border: 0;
        font-size: 20px;
        color: #1a4d80;
        min-width: 40px;
        background: transparent;
    }
    .tab-styling-btns a {
        font-size: 15px;
        background: #e9e9e9;
        text-decoration: none;
    }
    .input-group.tab-styling input {
        border: 0;
    }
    .form-group.main-tab .row {
        align-items: center;
        display: flex;
    }
</style>

<div class="modal whmcs-modal fade in" id="clientPasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content panel panel-primary">
            <div class="modal-header panel-heading" id="clientPasswordModalHeader">
                <button id="clientPasswordModalCloseSmall" type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="model-title" id="clientPasswordModalTitle">Changing password for: <span id="clientName">N/A</span> (<span id="clientEmail">(N/A)</span>)</h4>
            </div>
            <div class="modal-body panel-body" id="clientPasswordModalBody">
                <div class="alert alert-danger admin-modal-error clientPasswordModalAlert" style="display: none;"></div>
                <div class="admin-tabs-v2">
                    <div class="tab-content">
                        <div class="tab-pane active">
                            <div class="form-group main-tab">
                                <div class="row">
                                    <div class="col-md-3 col-sm-5">
                                        <label for="newPasswordInput" class="control-label">New Password</label>
                                    </div>
                                    <div class="col-md-9 col-sm-7">
                                        <div class="input-group tab-styling">
                                            <input type="text" id="newPasswordInput1" class="form-control" placeholder="Click on generate button for autofill">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group main-tab">
                                <div class="row">
                                    <div class="col-md-3 col-sm-5">
                                        <label for="newPasswordInput" class="control-label">Confirm Password</label>
                                    </div>
                                    <div class="col-md-9 col-sm-7">
                                        <div class="input-group tab-styling">
                                            <input type="text" id="newPasswordInput2" class="form-control" placeholder="Click on generate button for autofill">
                                                                                    <div class="tab-styling-btns">
                                                                                        <button onclick="copyInputData()" class="copy-button"><i class="glyphicon pe-7s-copy-file"></i></button>
                                                                                    </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
{*                            <div class="row">*}
{*                                <div class="offset-2 col-md-8">*}
{*                                    <div class="form-check">*}
{*                                        <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">*}
{*                                        <label class="form-check-label" for="flexCheckDefault">Send email to user</label>*}
{*                                    </div>*}
{*                                </div>*}
{*                            </div>*}
                        </div>
                    </div>
                </div>
                <input type="hidden" id="userId" value="">
            </div>
            <div class="modal-footer panel-footer" id="clientPasswordModalFooter">
                <div id="modalFooterLeft"></div>
                <div class="pull-left loader" id="clientPasswordModalLoader" style="display: none;">
                    <i class="fas fa-circle-notch fa-spin"></i>
                    Loading...
                </div>
                <button id="clientPasswordModalClose" type="button" class="btn btn-default" data-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn btn-primary modal-submit" id="generatePassword">Generate</button>
                <button type="button" class="btn btn-primary modal-submit" id="btnSave">Change</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {

        console.log('Constructed URL:', '{$moduleLink}&action=data');
        //
        $('.btn-permissions').each(function (i, obj) {
            $(this).after('<a href="#" data-user-id="' + $(this).attr('data-user-id') + '" class="btn btn-default changePasswordBtn">Change Password</a>');
        });

        $('.open-modal.manage-user').each(function (i, obj) {
            let id = $(this).attr('href').match(/\d+$/)[0];
            $(this).after('<a href="#" data-user-id="' + id + '" class="btn btn-default btn-sm changePasswordBtn">Change Pass</a>');
        });

        $('.changePasswordBtn').on('click', function (e) {
            e.preventDefault();
            let userID = $(this).attr('data-user-id');
            let first_name = $(this).closest('tr').find('.first').text();
            let last_name = $(this).closest('tr').find('.last').text();
            let user_email = $(this).closest('tr').find('.email').text();


            $.ajax({
                type: 'POST',
                url: '{$moduleLink}&action=data',
                data: 'user=' + userID + '&csrfToken={$csrfToken}',
                beforeSend: function () {
                    $('#modalChangePwLoader').fadeIn();
                    $('.modalChangePwAlert').removeClass('alert-success').addClass('alert-danger').html('').hide();
                    $('#newPasswordInput').val('');
                },
                success: function (res) {
                    $('#userId').val(userID);
                    $('#clientName').html(first_name + ' ' + last_name);
                    $('#clientEmail').html(user_email);
                },
                complete: function () {
                    $('#clientPasswordModalLoader').fadeOut();
                    $('#clientPasswordModal').modal('show');
                }
            });
        });

        $('#btnSave').on('click', function (e) {
            e.preventDefault();
            let userID = $('#userId').val();
            // let userID = '44';
            let password1 = $('#newPasswordInput1').val();
            let password2 = $('#newPasswordInput2').val();

            // console.log('pass1: ',password1,' - pass2: ',password2);

            if (password1 !== password2) {
                $('.clientPasswordModalAlert').removeClass('alert-success').addClass('alert-danger').html('Passwords do not match').fadeIn();
                return;
            }
            let password = encodeURIComponent(password1);
            // let sendEmail = $('#flexCheckDefault').prop('checked');

            $('#flexCheckDefault').val(userID);

            {*console.log('id: ',userID,' - pass: ',password,' - token: ','{$csrfToken}');*}
            {*console.log('link: ','{$moduleLink}&action=change');*}

            $.ajax({
                type: 'POST',
                url: '{$moduleLink}&action=change',
                data: 'user=' + userID + '&password=' + password + '&csrfToken={$csrfToken}',
                success: function (res) {

                    // console.log('res: ',res)

                    if (res.status === 'success') {
                        $('.clientPasswordModalAlert').removeClass('alert-danger').addClass('alert-success');
                        $('#newPasswordInput1').val('');
                        $('#newPasswordInput2').val('');

                        // if (sendEmail) {
                        //     sendEmailToUser(userID);
                        // }
                    } else {
                        $('.clientPasswordModalAlert').removeClass('alert-success').addClass('alert-danger');
                    }
                    $('.clientPasswordModalAlert').html(res.description).fadeIn();
                }
            });
        });

        // function sendEmailToUser(userID) {
        //     $.ajax({
        //         type: 'POST',
        //         url: '/modules/addons/client_password/lib/SendEmail/sendemail.php',
        //         data: {
        //             send_email: 'yes',
        //             userID: userID
        //         },
        //         success: function (response) {
        //             console.log('Email sent successfully',response);
        //         },
        //         error: function (xhr, status, error) {
        //             console.error('Error sending email:', error);
        //         }
        //     });
        // }

        $('#generatePassword').on('click', function (e) {
            e.preventDefault();
            const length = '{$passwordLength}';
            let charset = "abcdefghijklmnopqrstuvwxyz", retVal  = "";

            {if $useCapitalLetters}charset += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';{/if}
            {if $useNumbers}charset += '0123456789';{/if}
            {if $useSpecialCharacters}charset += '+-*/$£%!&';{/if}

            for (let i = 0, n = charset.length; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * n));
            }

            // Fill both password input fields
            $('#newPasswordInput1').val(retVal);
            $('#newPasswordInput2').val(retVal);
        });

        $('#newPasswordInput').keyup(function (e) {
            if (e.which === 13) {
                $('#btnSave').click();
            }
        });

        copyInputData();

    });

    function copyInputData() {
        var newPasswordInput = document.getElementById("newPasswordInput2");
        newPasswordInput.select();
        document.execCommand("copy");
        newPasswordInput.setSelectionRange(0, 0);
        var copyButton = document.querySelector('.copy-button');
        copyButton.innerHTML = 'Copyed!';
        setTimeout(function() {
            copyButton.innerHTML = '<i class="glyphicon pe-7s-copy-file"></i>';
        }, 1500);
    }
</script>