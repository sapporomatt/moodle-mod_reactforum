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
        $(".react-btn").click(function ()
        {
            var parentNode = $(this).parent();
            var numberNode = $(this).next("span");
            numberNode.html("<img src='pix/loading.gif'>");

            $.post("react_ajax.php",
                {
                    sesskey: M.cfg.sesskey,
                    post: parentNode.attr("post-id"),
                    reaction: parentNode.attr("reaction-id")
                },
                function (response)
                {
                    if (response.status)
                    {

                        $('.reaction-container[post-id=' + parentNode.attr("post-id") + '] button').removeClass("btn-primary").removeClass("btn-default");

                        for (i = 0; i < response.data.length; i++)
                        {
                            $('.reaction-container[post-id=' + response.data[i].post_id + '][reaction-id=' + response.data[i].reaction_id + '] span').html(response.data[i].count);

                            var $button = $('.reaction-container[post-id=' + response.data[i].post_id + '][reaction-id=' + response.data[i].reaction_id + '] button');

                            if (response.data[i].reacted) {
                                $button.addClass('btn-primary');
                            }
                            else {
                                $button.addClass('btn-default');
                            }

                            if (response.data[i].enabled) {
                                $button.prop('disabled', false);
                            }
                            else {
                                $button.prop('disabled', true);
                            }
                        }
                    }
                });
        });
    });
});