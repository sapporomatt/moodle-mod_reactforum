// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains a custom renderer class used by the reactforum module.
 *
 * @package   mod_reactforum
 * @copyright  2017 (C) VERSION2, INC.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(['jquery'], function ($)
{
    $(document).ready(function ()
    {
        var $maindiv = $('div#fgroup_id_reactions');
        var $area = $maindiv.find('fieldset');

        var $filepicker = $('div#fitem_id_reactionimage');
        $filepicker.hide();

        var reaction_type = 'text';
        var reactions = [];
        var level = '';

        $.prepare_text_reactions = function()
        {
            if(reaction_type !== 'text')
            {
                return;
            }

            var $reactioninput = $('<input type="text">')
                .attr('class', 'reaction reaction-text')
                .attr('reaction-id', '0')
                .attr('name', 'reactions[new][]');
            var $deletebtn = $('<input type="button">')
                .val(M.str.reactforum.reactions_delete);
            var $reactioninputs_div = $('<div>').attr('class', 'reaction-input')
                .append($reactioninput)
                .append($deletebtn);

            var $inputcontainer = $('<div>').attr('id', 'reactions-container');

            var $addbtn = $('<input type="button">')
                .val(M.str.reactforum.reactions_add);

            $addbtn.click(function()
            {
                var $newelement = $reactioninputs_div.clone(true, true);
                $newelement.find('input.reaction-text').val('');

                $inputcontainer.append($newelement);
                $newelement.find('input.reaction-text').focus();
            });

            $deletebtn.click(function()
            {
                var reaction_id = $(this).siblings('input.reaction-text').attr('reaction-id');

                if(reaction_id !== '0' && !confirm(M.str.reactforum.reactions_delete_confirmation))
                {
                    return;
                }

                if(reaction_id !== '0')
                {
                    $(this).siblings('input.reaction-text')
                        .attr('type', 'hidden')
                        .attr('name', 'reactions[delete][]')
                        .val(reaction_id);

                    $(this).closest('div.reaction-input').hide();
                }
                else
                {
                    $(this).closest('div.reaction-input').remove();
                }
            });

            $area.html($inputcontainer)
                .append($addbtn);

            if(reactions.length > 0)
            {
                $inputcontainer.html('');

                $.each(reactions, function(index, reaction)
                {
                    var $newelement = $reactioninputs_div.clone(true, true);
                    $newelement.find('input.reaction-text')
                        .attr('reaction-id', reaction.id)
                        .val(reaction.value);

                    if(reaction.id === '0')
                    {
                        $newelement.find('input.reaction-text').attr('name', 'reactions[new][]');
                    }
                    else
                    {
                        $newelement.find('input.reaction-text')
                            .attr('name', '')
                            .change(function()
                        {
                            $(this).attr('name', 'reactions[edit][' + $(this).attr('reaction-id') + ']');
                        });
                    }

                    $inputcontainer.append($newelement);
                });
            }
        };

        $.prepare_image_reactions = function()
        {
            if(reaction_type !== 'image')
            {
                return;
            }

            $area.html('');

            var $input = $('input#id_reactionimage');
            var $temp_element = $input.prev().find('div.filepicker-filelist div.filepicker-filename');
            var temp_html = $temp_element.html();
            var editid = 0;

            var $editheader = $('<h4/>');
            $editheader.html(M.str.reactforum.reactions_selectfile)
                .addClass('reaction-img-edit')
                .hide();

            var $cancelbtn = $('<input type="button"/>');
            $cancelbtn.val(M.str.reactforum.reactions_cancel)
                .addClass('reaction-img-edit')
                .click(function()
                {
                    editid = 0;
                    $editheader.hide();
                    $('.reaction-img-change-btn').prop('disabled', false);
                });

            $editheader.append($cancelbtn)
                .insertBefore($filepicker.find('input.fp-btn-choose'));

            // When new file uploaded
            $input.change(function()
            {
                var $filename = $(this).prev().find('div.filepicker-filename a');

                if(typeof $filename.attr('href') === 'undefined')
                {
                    return;
                }

                $.post(M.cfg.wwwroot + '/mod/reactforum/reactionimg_movetemp.php',
                    {
                        'url': $filename.attr('href')
                    }, function(tempfileid)
                    {
                        if(editid === 0)    // upload new reaction
                        {
                            var $newimg = $("<img/>");
                            $newimg.attr('alt', $filename.html())
                                .addClass('reaction-img')
                                .attr('src', $filename.attr('href'));

                            var $deletebtn = $('<input type="button"/>');
                            $deletebtn.val(M.str.reactforum.reactions_delete)
                                .click(function()
                                {
                                    $(this).closest('div.reaction-item').remove();
                                });

                            var $hiddenelement = $('<input type="hidden" name="reactions[new][]"/>');
                            $hiddenelement.addClass('reaction')
                                .val(tempfileid);

                            var $reaction_div = $('<div/>');
                            $reaction_div.addClass('reaction-item')
                                .append($newimg)
                                .append($deletebtn)
                                .append($hiddenelement);

                            $area.append($reaction_div);
                        }
                        else if(editid > 0) // upload new image for existing reaction
                        {
                            var $editdiv = $area.find('div#reaction-item-' + editid);
                            $editdiv.find('img.reaction-img')
                                .attr('src', $filename.attr('href'));

                            $area.find('input#reaction-image-edit-' + editid)
                                .val(tempfileid);

                            $editheader.hide();

                            editid = 0;
                            $('.reaction-img-change-btn').prop('disabled', false);
                        }
                    }, 'text');

                $temp_element.html(temp_html);
            });

            // Editing discussion
            $.each(reactions, function(index, reaction)
            {
                var $img = $('<img/>');
                $img.attr('alt', reaction.id)
                    .attr('src', M.cfg.wwwroot + '/mod/reactforum/reactionimg.php?id=' + reaction.id + '&sesskey=' + M.cfg.sesskey)
                    .addClass('reaction-img');

                var $changebtn = $('<input type="button"/>');
                $changebtn
                    .addClass('reaction-img-change-btn')
                    .val(M.str.reactforum.reactions_changeimage)
                    .click(function()
                    {
                        $('.reaction-img-change-btn').prop('disabled', false);
                        $(this).prop('disabled', true);

                        editid = reaction.id;
                        $editheader.show();
                    });

                var $deletebtn = $('<input type="button"/>');
                $deletebtn.val(M.str.reactforum.reactions_delete)
                    .click(function()
                    {
                        if(confirm(M.str.reactforum.reactions_delete_confirmation))
                        {
                            var $deletevalue = $('<input type="hidden"/>');
                            $deletevalue.attr('name', 'reactions[delete][]')
                                .val(reaction.id);

                            $area.append($deletevalue);

                            editid = 0;
                            $('.reaction-img-change-btn').prop('disabled', false);

                            $(this).closest('div.reaction-item').remove();
                        }
                    });

                var $edit = $('<input type="hidden"/>');
                $edit.attr('name', 'reactions[edit][' + reaction.id + ']')
                    .attr('id', 'reaction-image-edit-' + reaction.id)
                    .addClass('reaction')
                    .val('0');

                var $reaction_div = $('<div/>');
                $reaction_div.addClass('reaction-item')
                    .attr('id', 'reaction-item-' + reaction.id)
                    .append($img)
                    .append($changebtn)
                    .append($deletebtn)
                    .append($edit);

                $area.append($reaction_div);
            });

            $filepicker.show();
        };

        $("input[name='reactiontype']").change(function(e)
        {
            $filepicker.hide();

            if($('input.reaction').length > 0)
            {
                if(!confirm(M.str.reactforum.reactionstype_change_confirmation))
                {
                    $(this).prop('checked', false);
                    $('input[name="reactiontype"][value="' + reaction_type + '"]').prop('checked', true);
                    return false;
                }
            }

            if($(this).val() !== 'text' && $(this).val() !== 'image' && $(this).val() !== 'none' && $(this).val() !== 'discussion')
            {
                return;
            }

            reaction_type = $(this).val();

            if(reaction_type === 'text')
            {
                reactions = [ { id: '0', value: ''} ];
                $.prepare_text_reactions();
            }
            else if(reaction_type === 'image')
            {
                reactions = [];
                $.prepare_image_reactions();
            }

            if(reaction_type === 'none' || reaction_type === 'discussion')
            {
                reactions = [];
                $maindiv.hide();
            }
            else
            {
                $maindiv.show();
            }
        });

        if(typeof reactions_oldvalues !== 'undefined')
        {
            reactions = reactions_oldvalues.reactions;
            reaction_type = reactions_oldvalues.type;
            level = reactions_oldvalues.level;

            $maindiv.hide();
            if(reaction_type === 'text')
            {
                $('input#id_reactiontype_text').prop('checked', true);
                $.prepare_text_reactions();
                $maindiv.show();
            }
            else if(reaction_type === 'image')
            {
                $('input#id_reactiontype_image').prop('checked', true);
                $.prepare_image_reactions();
                $maindiv.show();
            }
        }
        else
        {
            $maindiv.hide();
        }
    });
});