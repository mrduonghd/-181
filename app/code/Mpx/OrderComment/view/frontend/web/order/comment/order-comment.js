define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'jquery/ui',
], function ($,modal) {
    $.widget('mage.OrderComment', {
        /**
         * Widget initialization
         * @private
         */
        _create: function () {
            self = this;
            var textEdit = $.mage.__('Edit');
            var textDelete = $.mage.__('Delete');
            /**
             * Add comment
             */
            $(".order-comment-add").click(function () {
                $.ajax({
                    url: self.options.urlOrderCommentAdd,
                    method: "POST",
                    dataType: 'json',
                    async: false,
                    data: {
                        comment_content: $("#history_comment").val()
                    },
                    success: function (response) {
                        if (response.error) {
                            popupError(response.message);
                        } else {
                            $(".wk-mp-column .list-comment").prepend('<li class="note-list-item" id="note-list-item-'+ response['comment_id'] +'">' +
                                '<span class ="note-list-data">'+ response['date'] +'</span>' + '&nbsp;' +
                                '<span class ="note-list-time">'+ response['time'] +'</span>' + '&nbsp;' +
                                '<span><a class="order-comment-edit" id="order-comment-edit-'+ response['comment_id'] +'">'+ textEdit +'</a></span>'+ '&nbsp;' +
                                '<span><a class="order-comment-delete" id="order-comment-delete-'+ response['comment_id'] +'">'+ textDelete +'</a></span>'+
                                '<div class="order-comment-comment" id="note-list-comment-'+ response['comment_id'] +'">'+ response['comment_content'] +'</div>'
                                + '</li>');
                            $("#history_comment").val('');
                        }
                    },
                    error: function () {
                        popupError($.mage.__('Failed to add the comment.'));
                    }
                });
            });

            /**
             * Edit Comment Order
             */
            $('.wk-mp-column').on('click','.list-comment .order-comment-edit',function () {
                var idCommentEdit = $(this).attr('id').replace('order-comment-edit-','');
                $(".input-text-comment-edit").val($('#note-list-comment-'+idCommentEdit).text());
                var statusCommentEdited = $.mage.__('(Comment Edited)');
                var optionsPopUpEdit = {
                    type: 'popup',
                    responsive: true,
                    title: self.options.titlePopupEdit,
                    buttons: [{
                        text: $.mage.__('Save'),
                        class: 'action primary accept',

                        /**
                         * Click edit comment.
                         */
                        click: function () {
                            $.ajax({
                                url: self.options.urlOrderCommentEdit,
                                method: "POST",
                                dataType: 'json',
                                async: false,
                                data: {
                                    comment_id: idCommentEdit,
                                    comment_content: $(".input-text-comment-edit").val()
                                },
                                success: function (response) {
                                    if (response.error) {
                                        popupError(response.message);
                                    } else {
                                        $("#note-list-item-"+response['comment_id']).html('' +
                                            '<span class ="note-list-data">'+ response['date'] +'</span>' + '&nbsp;' +
                                            '<span class ="note-list-time">'+ response['time'] +'</span>' + '&nbsp;' +
                                            '<span><a class="order-comment-edit" id="order-comment-edit-'+ response['comment_id'] +'">'+ textEdit +'</a></span>'+ '&nbsp;' +
                                            '<span><a class="order-comment-delete" id="order-comment-delete-'+ response['comment_id'] +'">'+ textDelete +'</a></span>'+
                                            '<div class="order-comment-comment" id="note-list-comment-'+ response['comment_id'] +'">'+ response['comment_content'] +'</div>' +
                                            '<div id="status-comment-edit-'+ response['comment_id'] +'">'+ statusCommentEdited +'</div>');
                                    }
                                },
                                error: function () {
                                    popupError($.mage.__('Failed to save the comment.'));
                                }
                            });
                            this.closeModal(true);
                        }
                    }, {
                        text: $.mage.__('Cancel'),
                        class: 'action',

                        /**
                         * Click close popup.
                         */
                        click: function () {
                            this.closeModal(true);
                        }
                    }]
                };
                modal(optionsPopUpEdit, $("#form-order-comment-edit"));
                $('#form-order-comment-edit').modal('openModal');
            });
            /***
             * Delete Comment Order
             */
            $(".wk-mp-column").on('click','.list-comment .order-comment-delete',function () {
                var idCommentDelete = $(this).attr('id').replace('order-comment-delete-','');
                var statusCommentDelted = $.mage.__('(Comment Deleted)');
                var optionsPopUpDelete = {
                    type: 'popup',
                    responsive: true,
                    title: self.options.titlePopupDelete,
                    buttons: [{
                        text: $.mage.__('OK'),
                        class: 'action primary accept',

                        /**
                         * Click delete comment.
                         */
                        click: function () {
                            $.ajax({
                                url: self.options.urlOrderCommentDelete,
                                method: "POST",
                                dataType: 'json',
                                async: false,
                                showLoader: true,
                                data: {
                                    comment_id: idCommentDelete,
                                },
                                success: function (response) {
                                    if (response.error) {
                                        popupError(response.message);
                                    } else {
                                        $("#note-list-item-"+response['comment_id']).html('' +
                                            '<span class ="note-list-data">'+ response['date'] +'</span>' + '&nbsp;' +
                                            '<span class ="note-list-time">'+ response['time'] +'</span>' + '&nbsp;' +
                                            '<span id="note-list-status-'+ response['comment_id'] +'">'+ statusCommentDelted +'</span>')
                                    }
                                },
                                error: function () {
                                    popupError($.mage.__('Failed to delete the comment.'));
                                }
                            });
                            this.closeModal(true);
                        }
                    }, {
                        text: $.mage.__('Cancel'),
                        class: 'action',

                        /**
                         * Click close popup.
                         */
                        click: function () {
                            this.closeModal(true);
                        }
                    }]
                };
                modal(optionsPopUpDelete, $('#form-order-comment-delete'));
                $("#form-order-comment-delete").modal('openModal');
            });

            function popupError(message)
            {
                var optionsPopUpError = {
                    type: 'popup',
                    responsive: true,
                    title: $.mage.__('Message'),
                    buttons: [{
                        text: $.mage.__('Close'),
                        class: 'action primary accept',

                        /**
                         * Click close popup.
                         */
                        click: function () {
                            this.closeModal(true);
                        }
                    }]
                };
                $('.content-message-error').text(message);
                modal(optionsPopUpError, $('#form-order-comment-error'));
                $("#form-order-comment-error").modal('openModal');
            }
        }
    });

    return $.mage.OrderComment;
});
