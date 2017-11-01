<?php

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
 * Strings for component 'reactforum', language 'ja', branch 'MOODLE_33_STABLE'
 *
 * @package   mod_reactforum
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityoverview'] = '新しいReactフォーラム投稿があります。';
$string['addanewdiscussion'] = '新しいディスカッショントピックを追加する';
$string['addanewquestion'] = '新しい質問を追加する';
$string['addanewtopic'] = '新しいトピックを追加する';
$string['advancedsearch'] = '高度な検索';
$string['allreactforums'] = 'すべてのReactフォーラム';
$string['allowdiscussions'] = '{$a} はこのReactフォーラムに投稿できますか?';
$string['allowsallsubscribe'] = 'このReactフォーラムではすべてのユーザが購読するかどうか選択できます。';
$string['allowsdiscussions'] = 'このReactフォーラムでは1人1件のディスカッショントピックを開始することができます。';
$string['allsubscribe'] = 'すべてのReactフォーラムを購読する';
$string['allunsubscribe'] = 'すべてのReactフォーラムの購読を解除する';
$string['alreadyfirstpost'] = 'このディスカッションにはすでに最初の投稿があります。';
$string['anyfile'] = 'すべてのファイル';
$string['areaattachment'] = '添付ファイル';
$string['areapost'] = 'メッセージ';
$string['attachment'] = '添付ファイル';
$string['attachment_help'] = 'あなたは1つまたはそれ以上のファイルをReactフォーラム投稿に任意で添付することができます。あなたがイメージを添付した場合、メッセージの下に表示されます。';
$string['attachmentnopost'] = 'あなたは投稿IDなしで添付ファイルをエクスポートできません。';
$string['attachments'] = '添付ファイル';
$string['attachmentswordcount'] = '添付および文字カウント';
$string['blockafter'] = 'ブロッキングまでの投稿閾値';
$string['blockafter_help'] = 'この設定では指定された時間内にユーザが投稿できる投稿数を指定します。ケイパビリティ「mod/reactforum:postwithoutthrottling」が割り当てられたユーザは投稿制限から除外されます。';
$string['blockperiod'] = 'ブロッキング期間';
$string['blockperioddisabled'] = 'ブロックしない';
$string['blockperiod_help'] = '指定された時間内に指定された投稿数以上を投稿した場合、学生の投稿を拒否することができます。ケイパビリティ「mod/reactforum:postwithoutthrottling」が割り当てられたユーザは投稿制限から除外されます。';
$string['blogreactforum'] = 'ブログフォーマットで表示される標準Reactフォーラム';
$string['bynameondate'] = '{$a->date} - {$a->name} の投稿';
$string['cannotadd'] = 'このReactフォーラムにディスカッションを追加できませんでした。';
$string['cannotadddiscussion'] = 'このReactフォーラムにディスカッションを追加するにはグループのメンバーである必要があります。';
$string['cannotadddiscussionall'] = 'あなたにはすべての参加者のための新しいディスカッショントピックを追加するパーミッションがありません。';
$string['cannotaddsubscriber'] = 'このReactフォーラムにID {$a} の購読者を追加できませんでした!';
$string['cannotaddteacherreactforumto'] = 'コースのセクションゼロに対してコンバートされた教師Reactフォーラムインスタンスを追加できませんでした。';
$string['cannotcreatediscussion'] = '新しいディスカッションを作成できませんでした。';
$string['cannotcreateinstanceforteacher'] = '教師Reactフォーラムに対して新しいコースモジュールインスタンスを作成できませんでした。';
$string['cannotdeletepost'] = 'あなたはこの投稿を削除できません!';
$string['cannoteditposts'] = 'あなたは他のユーザの投稿を編集できません!';
$string['cannotfinddiscussion'] = 'このReactフォーラムのディスカッションが見つかりませんでした。';
$string['cannotfindfirstpost'] = 'このReactフォーラムの最初の投稿が見つかりませんでした。';
$string['cannotfindorcreatereactforum'] = 'サイトの主アナウンスメントReactフォーラムが見つからないか作成できません。';
$string['cannotfindparentpost'] = '投稿 {$a} の先頭親投稿が見つかりませんでした。';
$string['cannotmovefromsinglereactforum'] = '「トピック1件のシンプルなディスカッション」Reactフォーラムからはディスカッションを移動できません。';
$string['cannotmovenotvisible'] = 'Reactフォーラムは非表示です。';
$string['cannotmovetonotexist'] = 'あなたはこのReactフォーラムを移動できません - Reactフォーラムが存在しません!';
$string['cannotmovetonotfound'] = 'このコースには対象のReactフォーラムが見つかりませんでした。';
$string['cannotmovetosinglereactforum'] = '「トピック1件のシンプルなディスカッション」Reactフォーラムにはディスカッションを移動できません。';
$string['cannotpurgecachedrss'] = 'ソースまたは対象Reactフォーラムに関してキャッシュされたRSSフィードを削除できませんでした - あなたのファイルパーミッションを確認してください。';
$string['cannotremovesubscriber'] = 'このReactフォーラムからID {$a} の購読者を削除できませんでした!';
$string['cannotreply'] = 'あなたはこの投稿に返信できません。';
$string['cannotsplit'] = 'このReactフォーラムのディスカッションは分割できません。';
$string['cannotsubscribe'] = '申し訳ございません、あなたが購読するにはグループメンバーである必要があります。';
$string['cannottrack'] = 'Reactフォーラムの未読管理を停止できませんでした。';
$string['cannotunsubscribe'] = 'あなたをReactフォーラムから購読解除できませんでした。';
$string['cannotupdatepost'] = 'あなたはこの投稿を更新できません。';
$string['cannotviewpostyet'] = 'まだ投稿していないため、あなたはこのディスカッションで他の学生の質問を読むことはできません。';
$string['cannotviewusersposts'] = 'このユーザの投稿に関して、あなたが閲覧できるものはありません。';
$string['cleanreadtime'] = '古い投稿を既読とする時刻';
$string['clicktosubscribe'] = 'あなたはこのディスカッションを購読していません。購読するにはクリックしてください。';
$string['clicktounsubscribe'] = 'あなたはこのディスカッションを購読しています。購読解除するにはクリックしてください。';
$string['completiondiscussions'] = '学生はディスカッションを作成する必要があります:';
$string['completiondiscussionsdesc'] = '学生は少なくとも {$a} 件のディスカッションを作成する必要があります。';
$string['completiondiscussionsgroup'] = '必須ディスカッション数';
$string['completiondiscussionshelp'] = '完了に必要なディスカッション数';
$string['completionposts'] = '学生は次の件数のディスカッションまたは返信を投稿する必要があります:';
$string['completionpostsdesc'] = '学生は少なくとも {$a} 件のディスカッションまたは返信を投稿する必要があります。';
$string['completionpostsgroup'] = '必須投稿数';
$string['completionpostshelp'] = '完了に必要なディスカッションまたは返信数';
$string['completionreplies'] = '学生は次の件数の返信を投稿する必要があります:';
$string['completionrepliesdesc'] = '学生は少なくとも {$a} 件の返信を投稿する必要があります。';
$string['completionrepliesgroup'] = '必須返信数';
$string['completionreplieshelp'] = '完了に必要な返信数';
$string['configcleanreadtime'] = '古い投稿を「既読」テーブルからクリアする時刻 (時) です。';
$string['configdigestmailtime'] = 'メール送信を選択したユーザに投稿内容を要約したメールが毎日送信されます。ここでは1日の内で何時に毎日のメールを送信するか設定します (この設定後に実行される次のcronがメールを送信します)。';
$string['configdisplaymode'] = '表示モードが設定されていない場合、ディスカッションで使用されるデフォルト表示モードです。';
$string['configenablerssfeeds'] = 'すべてのReactフォーラムのRSS使用を有効にします。ここで設定しても各ReactフォーラムでRSSフィードを手動で設定する必要があります。';
$string['configenabletimedposts'] = '新しいReactフォーラムディスカッションの表示期間の設定を許可したい場合、「Yes」を選択してください。';
$string['configlongpost'] = 'この文字長以上の長さ (HTMLは含まない) は長いとみなされます。サイトのフロントページ、ソーシャルフォーマット、ユーザプロファイルに表示される投稿内容の長さはreactforum_shortpostとreactforum_longpostの値の間に短くされます。';
$string['configmanydiscussions'] = 'Reactフォーラム1ページあたりに表示されるディスカッションの最大数です。';
$string['configmaxattachments'] = '投稿ごとに許可されるデフォルトの最大添付ファイル数です。';
$string['configmaxbytes'] = 'すべてのReactフォーラムの添付ファイルに関するデフォルト最大サイズ (コース制限および他のローカル設定に従います)';
$string['configoldpostdays'] = '古い投稿を既読とする日数です。';
$string['configreplytouser'] = 'Reactフォーラムの投稿がメール送信される場合、受信者がReactフォーラムを介さず個人的に返信できるようメールにユーザのメールアドレスを表示しますか? 「Yes」に設定した場合でもユーザはプロファイルページでメールアドレスを隠すよう設定することができます。';
$string['configrssarticlesdefault'] = 'RSSフィードが有効にされた場合、デフォルト投稿数 (ディスカッションまたは投稿) を設定してください。';
$string['configrsstypedefault'] = 'RSSフィードが有効にされた場合、デフォルト活動タイプを設定してください。';
$string['configshortpost'] = 'この文字長以下の長さ (HTMLは含まない) は短いとみなされます (下記参照)。';
$string['configtrackingtype'] = '未読管理のデフォルト設定';
$string['configtrackreadposts'] = 'ユーザごとに未読管理したい場合、「Yes」を選択してください。';
$string['configusermarksread'] = '「Yes」に設定した場合、ユーザは投稿を手動で既読にする必要があります。「No」に設定した場合、投稿が閲覧された時点で既読にされます。';
$string['confirmsubscribe'] = '本当にReactフォーラム「 {$a} 」を購読してもよろしいですか?';
$string['confirmsubscribediscussion'] = '本当にReactフォーラム「 {$a->reactforum} 」内のディスカッション「 {$a->discussion} 」を購読してもよろしいですか?';
$string['confirmunsubscribe'] = '本当にReactフォーラム「 {$a} 」から購読解除してもよろしいですか?';
$string['confirmunsubscribediscussion'] = '本当にReactフォーラム「 {$a->reactforum} 」内のディスカッション「 {$a->discussion} 」から購読解除してもよろしいですか?';
$string['couldnotadd'] = '不明なエラーのためあなたの投稿を追加できませんでした。';
$string['couldnotdeletereplies'] = '申し訳ございません、返信済みのため削除できませんでした。';
$string['couldnotupdate'] = '不明なエラーのため投稿を更新できませんでした。';
$string['crontask'] = 'Reactフォーラムメーリングおよびメンテナンスジョブ';
$string['delete'] = '削除';
$string['deleteddiscussion'] = 'ディスカッショントピックが削除されました。';
$string['deletedpost'] = '投稿が削除されました。';
$string['deletedposts'] = '投稿が削除されました。';
$string['deletesure'] = 'この投稿を削除してもよろしいですか?';
$string['deletesureplural'] = 'この投稿およびすべての返信を削除してもよろしいですか? (投稿数 {$a})';
$string['digestmailheader'] = 'これは {$a->sitename} Reactフォーラムの新しい投稿に関するあなたのデイリーダイジェストです。あなたのデフォルトのReactフォーラムメールプリファレンスを変更するには {$a->userprefs} に移動してください。';
$string['digestmailpost'] = 'あなたのReactフォーラムダイジェストプリファレンスを変更する';
$string['digestmailpostlink'] = 'あなたのReactフォーラムダイジェストプリファレンスを変更する: {$a}';
$string['digestmailprefs'] = 'あなたのユーザプロファイル';
$string['digestmailsubject'] = '{$a}: Reactフォーラムダイジェスト';
$string['digestmailtime'] = 'ダイジェストメールを送信する時刻';
$string['digestsentusers'] = 'メールダイジェストが {$a} 名のユーザに正常に送信されました。';
$string['disallowsubscribe'] = '購読不可';
$string['disallowsubscribeteacher'] = '購読不可 (教師を除く)';
$string['disallowsubscription'] = '購読';
$string['disallowsubscription_help'] = 'あなたがディスカッションを購読できないようこのReactフォーラムが設定されました。';
$string['discussion'] = 'ディスカッション';
$string['discussionlocked'] = 'このディスカッションはロックされているため、あなたは返信することはできません。';
$string['discussionlockingdisabled'] = 'ディスカッションをロックしない';
$string['discussionlockingheader'] = 'ディスカッションロッキング';
$string['discussionmoved'] = 'このディスカッションは 「 {$a} 」に移動されました。';
$string['discussionmovedpost'] = 'このディスカッションはReactフォーラム「 <a href="{$a->reactforumhref}">{$a->reactforumname}</a> 」の<a href="{$a->discusshref}">ここ</a>に移動されました。';
$string['discussionname'] = 'ディスカッション名';
$string['discussionnownotsubscribed'] = '{$a->name} には「 {$a->reactforum} 」の「 {$a->discussion} 」に関する新しい投稿は通知されません。';
$string['discussionnowsubscribed'] = '{$a->name} に「 {$a->reactforum} 」の「 {$a->discussion} 」に関する新しい投稿が通知されます。';
$string['discussionpin'] = 'ピン留め';
$string['discussionpinned'] = 'ピン留め';
$string['discussionpinned_help'] = 'ピン留めディスカッションはReactフォーラムの最上部に表示されます。';
$string['discussions'] = 'ディスカッション';
$string['discussionsstartedby'] = '{$a} によって開始されたディスカッション';
$string['discussionsstartedbyrecent'] = '{$a} によって最近開始されたディスカッション';
$string['discussionsstartedbyuserincourse'] = '{$a->fullname} によって {$a->coursename} で開始されたディスカッション';
$string['discussionsubscribestart'] = 'このディスカッションの新しい投稿のコピーを私にメール送信してください';
$string['discussionsubscribestop'] = 'このディスカッションの新しい投稿のコピーを私にメール送信しないでください';
$string['discussionsubscription'] = 'ディスカッション購読';
$string['discussionsubscription_help'] = 'ディスカッションを購読することにより、あなたはこのディスカッションへの新しい投稿の通知を受信することができます。';
$string['discussionunpin'] = 'ピン留め解除';
$string['discussthistopic'] = 'このトピックを読む';
$string['displayend'] = '表示終了';
$string['displayend_help'] = 'この設定では特定の日付の後にReactフォーラム投稿を非表示にするかどうか指定します。管理者は常にReactフォーラム投稿を閲覧できることに留意してください。';
$string['displaymode'] = '表示モード';
$string['displayperiod'] = '表示期間';
$string['displaystart'] = '表示開始';
$string['displaystart_help'] = 'この設定では特定の日付からReactフォーラム投稿を表示するかどうか指定します。管理者は常にReactフォーラム投稿を閲覧できることに留意してください。';
$string['displaywordcount'] = '総単語数を表示する';
$string['displaywordcount_help'] = 'この設定ではそれぞれの投稿の総単語数を表示するかどうか指定します。';
$string['eachuserreactforum'] = '各人が1件のディスカッションを投稿する';
$string['edit'] = '編集';
$string['editedby'] = '{$a->name} により編集 - 最初の投稿日時 {$a->date}';
$string['editedpostupdated'] = '{$a} の投稿が更新されました。';
$string['editing'] = '編集';
$string['emaildigest_0'] = 'あなたはReactフォーラム投稿ごとに1通のメールを受信します。';
$string['emaildigest_1'] = 'あなたはそれぞれのReactフォーラム投稿に関する完全なコンテンツを含むメールダイジェストを1日1通受信します。';
$string['emaildigest_2'] = 'あなたはそれぞれのReactフォーラム投稿に関する件名を含むメールダイジェストを1日1通受信します。';
$string['emaildigestcompleteshort'] = '完全な投稿';
$string['emaildigestdefault'] = 'デフォルト ({$a})';
$string['emaildigestoffshort'] = 'ダイジェストなし';
$string['emaildigestsubjectsshort'] = '件名のみ';
$string['emaildigesttype'] = 'メールダイジェストオプション';
$string['emaildigesttype_help'] = 'あなたがそれぞれの投稿に関して受信する通知タイプです。

* デフォルト - あなたのプロファイルのダイジェスト設定に従います。あなたがプロファイルを更新した場合、ここで変更内容が反映されます。
* ダイジェストなし - あなたはReactフォーラム投稿ごとに1通のメールを受信します。
* ダイジェスト - 完全な投稿 - あなたはそれぞれのReactフォーラム投稿に関する完全なコンテンツを含むメールダイジェストを1日1通受信します。
* ダイジェスト - 件名のみ - あなたはそれぞれのReactフォーラム投稿に関する件名を含むメールダイジェストを1日1通受信します。';
$string['emaildigestupdated'] = 'Reactフォーラム「 {$a->reactforum} 」に関するメールダイジェストオプションが「 {$a->maildigesttitle} 」に変更されました。
{$a->maildigestdescription}';
$string['emaildigestupdated_default'] = 'あなたの「 {$a->maildigesttitle} 」のデフォルトプロファイル設定はReactフォーラム「 {$a->reactforum} 」に使用されました。
{$a->maildigestdescription}';
$string['emptymessage'] = 'あなたの投稿に問題があります。おそらく投稿が空白のままか、添付ファイルのサイズが大きすぎます。あなたの変更は保存されませんでした。';
$string['erroremptymessage'] = '投稿メッセージを空にすることはできません。';
$string['erroremptysubject'] = '投稿件名を空にすることはできません。';
$string['errorenrolmentrequired'] = 'このコンテンツにアクセスするにはあなたはこのコースに登録する必要があります。';
$string['errorwhiledelete'] = 'レコードの削除中にエラーが発生しました。';
$string['eventassessableuploaded'] = 'コンテンツが投稿されました。';
$string['eventcoursesearched'] = 'コースが検索されました。';
$string['eventdiscussioncreated'] = 'ディスカッションが作成されました。';
$string['eventdiscussiondeleted'] = 'ディスカッションが削除されました。';
$string['eventdiscussionmoved'] = 'ディスカッションが移動されました。';
$string['eventdiscussionpinned'] = 'ディスカッションがピン留めされました。';
$string['eventdiscussionsubscriptioncreated'] = 'ディスカッション購読が作成されました。';
$string['eventdiscussionsubscriptiondeleted'] = 'ディスカッション購読が削除されました。';
$string['eventdiscussionunpinned'] = 'ディスカッションがピン留め解除されました。';
$string['eventdiscussionupdated'] = 'ディスカッションが更新されました。';
$string['eventdiscussionviewed'] = 'ディスカッションが閲覧されました。';
$string['eventpostcreated'] = '投稿が作成されました。';
$string['eventpostdeleted'] = '投稿が削除されました。';
$string['eventpostupdated'] = '投稿が更新されました。';
$string['eventreadtrackingdisabled'] = '未読管理が無効にされました。';
$string['eventreadtrackingenabled'] = '未読管理が有効にされました。';
$string['eventsubscribersviewed'] = '購読者が閲覧されました。';
$string['eventsubscriptioncreated'] = '購読が作成されました。';
$string['eventsubscriptiondeleted'] = '購読が削除されました。';
$string['eventuserreportviewed'] = 'ユーザレポートが閲覧されました。';
$string['everyonecanchoose'] = 'すべてのユーザは購読を選択できます。';
$string['everyonecannowchoose'] = 'すべてのユーザは購読を選択できるようになりました。';
$string['everyoneisnowsubscribed'] = 'すべてのユーザがこのReactフォーラムを購読するようになりました。';
$string['everyoneissubscribed'] = 'すべてのユーザがこのReactフォーラムを購読します。';
$string['existingsubscribers'] = '既存の購読者';
$string['exportdiscussion'] = 'すべてのディスカッションをポートフォリオにエクスポートする';
$string['forcedreadtracking'] = '未読管理の強制を許可する';
$string['forcedreadtracking_desc'] = 'Reactフォーラムの未読管理の強制を許可します。特に多くのReactフォーラムおよび投稿があるコースに関してユーザのパフォーマンスが下がることになります。無効にした場合、前に強制が設定されたReactフォーラムは任意として扱われます。';
$string['forcesubscribed'] = 'このReactフォーラムは購読が強制されています。';
$string['forcesubscribed_help'] = 'あなたがディスカッションの購読を解除できないようこのReactフォーラムが設定されました。';
$string['reactforum'] = 'Reactフォーラム';
$string['reactforum:addinstance'] = '新しいReactフォーラムを追加する';
$string['reactforum:addnews'] = 'アナウンスメントを追加する';
$string['reactforum:addquestion'] = '質問を追加する';
$string['reactforum:allowforcesubscribe'] = '強制購読を許可する';
$string['reactforumauthorhidden'] = '投稿者 (非表示)';
$string['reactforumblockingalmosttoomanyposts'] = 'あなたは投稿数の上限に近づきつつあります。あなたは直近の {$a->blockperiod} に {$a->numposts} 回投稿しています。投稿数の上限は {$a->blockafter} 回です。';
$string['reactforumbodyhidden'] = 'あなたはこの投稿を閲覧できません。恐らく、あなたがまだディスカッションに投稿していない、最大編集時間を経過していない、ディスカッションが開始されていない、またはディスカッションの有効期限が切れています。';
$string['reactforum:canoverridediscussionlock'] = 'ロックされたディスカッションに返信する';
$string['reactforum:canposttomygroups'] = 'あなたがアクセスできるグループすべてに投稿できる';
$string['reactforum:createattachment'] = '添付を作成する';
$string['reactforum:deleteanypost'] = 'どの投稿でも削除する (いつでも)';
$string['reactforum:deleteownpost'] = '自分の投稿を削除する (期限内)';
$string['reactforum:editanypost'] = 'どの投稿でも編集する';
$string['reactforum:exportdiscussion'] = 'すべてのディスカッションをエクスポートする';
$string['reactforum:exportownpost'] = '自分の投稿をエクスポートする';
$string['reactforum:exportpost'] = '投稿をエクスポートする';
$string['reactforumintro'] = '説明';
$string['reactforum:managesubscriptions'] = '購読を管理する';
$string['reactforum:movediscussions'] = 'ディスカッションを移動する';
$string['reactforumname'] = 'Reactフォーラム名';
$string['reactforum:pindiscussions'] = 'ディスカッションをピン留めする';
$string['reactforumposts'] = 'Reactフォーラム投稿';
$string['reactforum:postwithoutthrottling'] = '投稿閾値を適用しない';
$string['reactforum:rate'] = '投稿を評価する';
$string['reactforum:replynews'] = 'アナウンスメントに返信する';
$string['reactforum:replypost'] = '投稿に返信する';
$string['reactforums'] = 'Reactフォーラム';
$string['reactforum:splitdiscussions'] = 'ディスカッションを分割する';
$string['reactforum:startdiscussion'] = '新しいディスカッションを開始する';
$string['reactforumsubjecthidden'] = '件名 (非表示)';
$string['reactforumtracked'] = '投稿は未読管理されています。';
$string['reactforumtrackednot'] = '投稿は未読管理されていません。';
$string['reactforumtype'] = 'Reactフォーラムタイプ';
$string['reactforumtype_help'] = 'Reactフォーラムには5つのタイプあります:

* トピック1件のシンプルなディスカッション - 誰でも返信できる単一のディスカッションです (分離グループには使用できません)。
* 各人が1件のディスカッションを投稿する - それぞれの学生が誰でも返信できる厳密に1つのディスカッショントピックを投稿できます。
* Q&AReactフォーラム - 学生は他の学生の投稿を読む前に自分の考え方を投稿する必要があります。
* ブログフォーマットで表示される標準Reactフォーラム - 誰でも常に新しいトピックを開始できる開かれたReactフォーラムです。ディスカッショントピックは1つのページに「このトピックを読む」リンクとして表示されます。
* 一般利用のための標準Reactフォーラム - 誰でも常に新しいトピックを開始できる開かれたReactフォーラムです。';
$string['reactforum:viewallratings'] = '個別のユーザから与えられた実評価すべてを表示する';
$string['reactforum:viewanyrating'] = 'すべてのユーザが受けた評価合計を表示する';
$string['reactforum:viewdiscussion'] = 'ディスカッションを表示する';
$string['reactforum:viewhiddentimedposts'] = '非表示の時間制限投稿を表示する';
$string['reactforum:viewqandawithoutposting'] = 'Q&A投稿を常に表示する';
$string['reactforum:viewrating'] = 'あなたが受けた評価合計を表示する';
$string['reactforum:viewsubscribers'] = '購読者を表示する';
$string['generalreactforum'] = '一般利用のための標準Reactフォーラム';
$string['generalreactforums'] = '一般Reactフォーラム';
$string['hiddenreactforumpost'] = '非表示Reactフォーラム投稿';
$string['inreactforum'] = '{$a}';
$string['introblog'] = '今後ブログエントリが利用できないため、このReactフォーラムの投稿はコース内のユーザブログから自動的にコピーされました。';
$string['intronews'] = '一般ニュースおよびアナウンスメント';
$string['introsocial'] = '投稿制限なしReactフォーラム';
$string['introteacher'] = '教師専用Reactフォーラム';
$string['invalidaccess'] = 'このページは正しくアクセスされていません。';
$string['invaliddigestsetting'] = '無効なメールダイジェストが設定されました。';
$string['invaliddiscussionid'] = 'ディスカッションIDが正しくないか存在しません。';
$string['invalidforcesubscribe'] = '無効な強制購読モードです。';
$string['invalidreactforumid'] = 'ReactフォーラムIDが正しくありません。';
$string['invalidparentpostid'] = '親投稿IDが正しくありません。';
$string['invalidpostid'] = '投稿ID ({$a}) が有効ではありません。';
$string['lastpost'] = '最新の投稿';
$string['learningreactforums'] = '学習Reactフォーラム';
$string['lockdiscussionafter'] = '次の休眠期間後、ディスカッションをロックする';
$string['lockdiscussionafter_help'] = '最後の返信以後 、指定された期間の経過後にディスカッションを自動でロックすることができます。

ロックされたディスカッションに返信するケイパビリティのあるユーザはディスカッションに返信することによりロックを解除することができます。';
$string['longpost'] = '長い投稿';
$string['mailnow'] = '編集遅延時間なしにReactフォーラム投稿通知を送信する';
$string['managesubscriptionsoff'] = '購読管理を終了する';
$string['managesubscriptionson'] = '購読を管理する';
$string['manydiscussions'] = '1ページあたりのディスカッション数';
$string['markalldread'] = 'このディスカッションの投稿すべてを既読にします。';
$string['markallread'] = 'このReactフォーラムの投稿すべてを既読にします。';
$string['markasreadonnotification'] = 'Reactフォーラム投稿通知を送信する場合';
$string['markasreadonnotification_help'] = 'Reactフォーラム投稿が通知される場合、あなたはReactフォーラム未読管理の目的として投稿を既読にするかどうか選択することができます。';
$string['markasreadonnotificationno'] = '投稿を既読にしない';
$string['markasreadonnotificationyes'] = '投稿を既読にする';
$string['markread'] = '既読にする';
$string['markreadbutton'] = '既読<br />にする';
$string['markunread'] = '未読にする';
$string['markunreadbutton'] = '未読<br />にする';
$string['maxattachments'] = '最大添付ファイル数';
$string['maxattachments_help'] = 'この設定ではReactフォーラム投稿に添付できる最大ファイル数を指定します。';
$string['maxattachmentsize'] = '最大添付ファイルサイズ';
$string['maxattachmentsize_help'] = 'この設定ではReactフォーラム投稿に添付できる最大ファイルサイズを指定します。';
$string['maxtimehaspassed'] = '申し訳ございません、この投稿 ({$a}) の最大編集回数を超えました!';
$string['message'] = 'メッセージ';
$string['messageinboundattachmentdisallowed'] = 'あなたの返信には添付を含みますがReactフォーラムが添付を許可していないため、投稿することはできません。';
$string['messageinboundfilecountexceeded'] = 'Reactフォーラム ({$a->reactforum->maxattachments}) で許可された最大添付数を超えているため、あなたの返信を投稿することはできません。';
$string['messageinboundfilesizeexceeded'] = '合計添付サイズ ({$a->filesize}) がReactフォーラムで許可された最大サイズ ({$a->maxbytes}) を超えているため、あなたの返信を投稿することはできません。';
$string['messageinboundreactforumhidden'] = '現在、Reactフォーラムを利用できないため、あなたの返信を投稿することはできません。';
$string['messageinboundnopostreactforum'] = 'あなたには {$a->reactforum->name} に投稿するパーミッションがないため、あなたの返信を投稿することはできません。';
$string['messageinboundthresholdhit'] = 'あなたの返信を投稿できません。あなたはこのReactフォーラムに設定された投稿閾値を超過しています。';
$string['messageprovider:digests'] = '購読Reactフォーラムダイジェスト';
$string['messageprovider:posts'] = '購読Reactフォーラム投稿';
$string['missingsearchterms'] = '次の検索語はこのメッセージのHTMLマークアップにのみ表示されます。';
$string['modeflatnewestfirst'] = '返信を新しいものからフラット表示する';
$string['modeflatoldestfirst'] = '返信を古いものからフラット表示する';
$string['modenested'] = '返信をネスト表示する';
$string['modethreaded'] = '返信をスレッド表示する';
$string['modulename'] = 'Reactフォーラム';
$string['modulename_help'] = 'Reactフォーラム活動モジュールにおいて参加者は非同期にディスカッションすることができます。例) 長期間に及ぶディスカッション

誰でもいつでも新しいディスカッションを開始することのできる標準Reactフォーラム、それぞれの学生が厳密に1つのディスカッションのみ開始することのできるReactフォーラムまたは他の学生の投稿を閲覧するためには学生が最初に投稿する必要のあるQ＆ＡReactフォーラム等、選択することのできるいくつかのReactフォーラムタイプがあります。教師はReactフォーラム投稿へのファイル添付を許可することができます。添付された画像はReactフォーラム投稿内に表示されます。

新しい投稿に関する通知を受信するできるよう参加者はReactフォーラムを購読することができます。教師は購読モードを任意、強制、自動、または停止に設定することができます。必要であれば設定された時間内に設定された投稿数以上を投稿できないよう学生をブロックすることができます。これは個人によるディスカッションの支配を防ぐことができます。

Reactフォーラム投稿は教師または学生 (ピア評価) によって評価することができます。評価は合計した後に最終評価として評定表に記録させることができます。

Reactフォーラムは下記のように使用することができます:

* 学生がお互いを知り合うためのソーシャルスペースとして
* コースのお知らせ用として (強制購読のニュースReactフォーラムを使用)
* コースコンテンツまたは読書素材のディスカッション用として
* 以前に対面セッションで触れた問題に関する継続的なオンラインディスカッション用として
* 教師専用Reactフォーラムとして (非表示Reactフォーラムを使用)
* チューターおよび学生がアドバイスを与えることのできるヘルプセンターとして
* 学生教師間の1対1のプライベートサポートエリアとして (1グループあたり1人のグループを使った分離グループを使用)
* 学外活動用として (例えば学生が熟考するための「頭の体操」および解決方法の提案)';
$string['modulenameplural'] = 'Reactフォーラム';
$string['more'] = '詳細';
$string['movedmarker'] = '(移動済み)';
$string['movethisdiscussionto'] = 'このディスカッションを移動する ...';
$string['mustprovidediscussionorpost'] = 'あなたはディスカッションIDまたは投稿IDをエクスポートに提供する必要があります。';
$string['myprofileotherdis'] = 'Reactフォーラムディスカッション';
$string['myprofileowndis'] = 'マイReactフォーラムディスカッション';
$string['myprofileownpost'] = 'マイReactフォーラム投稿';
$string['namenews'] = 'アナウンスメント';
$string['namenews_help'] = 'コースアナウンスメントReactフォーラムはお知らせのための特別Reactフォーラムです。コース作成時に自動手的に作成されます。コースには1つのアナウンスメントReactフォーラムのみ設置することができます。教師および管理者のみアナウンスメントを投稿することができます。「最新アナウンスメント」ブロックでは最新のアナウンスメントを表示します。';
$string['namesocial'] = 'ソーシャルReactフォーラム';
$string['nameteacher'] = '教師Reactフォーラム';
$string['newreactforumposts'] = '新しいReactフォーラム投稿';
$string['nextdiscussiona'] = '次のディスカッション: {$a}';
$string['noattachments'] = 'このReactフォーラムには添付ファイルがありません。';
$string['nodiscussions'] = 'このReactフォーラムにはまだディスカッショントピックはありません。';
$string['nodiscussionsstartedby'] = '{$a} から開始されたディスカッションはありません。';
$string['nodiscussionsstartedbyyou'] = 'あなたが開始したディスカッションはありません。';
$string['noguestpost'] = '申し訳ございません、ゲストは投稿できません。';
$string['noguestsubscribe'] = '申し訳ございません、ゲストは購読できません。';
$string['noguesttracking'] = '申し訳ございません、ゲストは未読管理オプションを設定できません。';
$string['nomorepostscontaining'] = 'これ以上「 {$a} 」 を含んだ投稿はありません。';
$string['nonews'] = 'まだ新しいアナウンスメントは投稿されていません。';
$string['noonecansubscribenow'] = '現在、購読は無効にされています。';
$string['nopermissiontosubscribe'] = 'あなたには購読者を閲覧するパーミッションがありません。';
$string['nopermissiontoview'] = 'あなたにはこの投稿を閲覧するパーミッションがありません。';
$string['nopostreactforum'] = '申し訳ございません、あなたはこのReactフォーラムに投稿できません。';
$string['noposts'] = '投稿はありません。';
$string['nopostsmadebyuser'] = '{$a} の投稿はありません。';
$string['nopostsmadebyyou'] = 'あなたの投稿はありません。';
$string['noquestions'] = 'このReactフォーラムにはまだ質問はありません。';
$string['nosubscribers'] = 'このReactフォーラムにはまだ購読者はいません。';
$string['notexists'] = 'ディスカッションはすでに存在しません。';
$string['nothingnew'] = '{$a} に新しい投稿はありません。';
$string['notingroup'] = '申し訳ございません、あなたがこのReactフォーラムを閲覧するにはグループに属している必要があります。';
$string['notinstalled'] = 'Reactフォーラムモジュールがインストールされていません。';
$string['notpartofdiscussion'] = 'この投稿はディスカッションの一部ではありません。';
$string['notrackreactforum'] = '投稿を未読管理しない';
$string['notsubscribed'] = '購読する';
$string['noviewdiscussionspermission'] = 'あなたにはこのReactフォーラムを閲覧するパーミッションがありません。';
$string['nowallsubscribed'] = '{$a} のすべてのReactフォーラムの購読を登録しました。';
$string['nowallunsubscribed'] = '{$a} のすべてのReactフォーラムの購読を解除しました。';
$string['nownotsubscribed'] = '{$a->name} には「 {$a->reactforum} 」の新しい投稿は通知されません。';
$string['nownottracking'] = '{$a->name} は 「 {$a->reactforum} 」を未読管理していません。';
$string['nowsubscribed'] = '{$a->name} には「 {$a->reactforum} 」の新しい投稿が通知されます。';
$string['nowtracking'] = '{$a->name} は現在「 {$a->reactforum} 」を未読管理しています。';
$string['numposts'] = '{$a} 投稿';
$string['olderdiscussions'] = '過去のディスカッション';
$string['oldertopics'] = '過去のトピック';
$string['oldpostdays'] = '投稿を既読とする日数';
$string['overviewnumpostssince'] = '最終ログイン以降の投稿数: {$a}';
$string['overviewnumunread'] = '合計未読数: {$a}';
$string['page-mod-reactforum-discuss'] = 'Reactフォーラムモジュールディスカッションスレッドページ';
$string['page-mod-reactforum-view'] = 'Reactフォーラムモジュールメインページ';
$string['page-mod-reactforum-x'] = 'すべてのReactフォーラムモジュールページ';
$string['parent'] = '親投稿を表示する';
$string['parentofthispost'] = 'この投稿の親';
$string['permalink'] = 'パーマリンク';
$string['pluginadministration'] = 'Reactフォーラム管理';
$string['pluginname'] = 'Reactフォーラム';
$string['postadded'] = '<p>あなたの投稿が正常に追加されました。</p>
<p>あなたが内容を変更したい場合、 {$a} 編集できます。</p>';
$string['postaddedsuccess'] = 'あなたの投稿が正常に追加されました。';
$string['postaddedtimeleft'] = 'あなたが内容を変更したい場合、 {$a} 編集できます。';
$string['postbymailsuccess'] = 'おめでとうございます、あなたの件名「 {$a->subject} 」のReactフォーラム投稿が正常に追加されました。あなたは {$a->discussionurl} で投稿を閲覧することができます。';
$string['postbymailsuccess_html'] = 'おめでとうございます、あなたの件名「 $a->subject 」の<a href="{$a->discussionurl}">Reactフォーラム投稿</a>が正常に投稿されました。';
$string['postbyuser'] = '{$a->post} by {$a->user}';
$string['postincontext'] = 'この投稿をコンテクスト内に表示する';
$string['postmailinfo'] = 'これはウェブサイト {$a} に投稿されたメッセージのコピーです。

返信するにはこのリンクをクリックしてください:';
$string['postmailinfolink'] = 'これは {$a->coursename} に投稿されたメッセージのコピーです。

返信するにはこのリンクをクリックしてください: {$a->replylink}';
$string['postmailnow'] = '<p>この投稿はすべてのReactフォーラム購読者にすぐに送信されます。</p>';
$string['postmailsubject'] = '{$a->courseshortname}: {$a->subject}';
$string['postrating1'] = '主に分離認識の傾向がある';
$string['postrating2'] = '分離認識と関連認識を同等に持っている';
$string['postrating3'] = '主に関連認識の傾向がある';
$string['posts'] = '投稿';
$string['postsmadebyuser'] = '{$a} による投稿';
$string['postsmadebyuserincourse'] = '{$a->coursename} における {$a->fullname} による投稿';
$string['posttoreactforum'] = 'Reactフォーラムに投稿する';
$string['posttomygroups'] = 'すべてのグループにコピーを投稿する';
$string['posttomygroups_help'] = 'あなたがアクセスすることのできるすべてのグループにこのメッセージのコピーを投稿します。あなたがアクセスすることのできないグループの参加者はこの投稿を閲覧することはできません。';
$string['postupdated'] = 'あなたの投稿が更新されました。';
$string['potentialsubscribers'] = '潜在的購読者';
$string['prevdiscussiona'] = '前のディスカッション: {$a}';
$string['processingdigest'] = 'ユーザ {$a} のメールダイジェストを処理中';
$string['processingpost'] = '投稿 {$a} を処理中';
$string['prune'] = '分割';
$string['prunedpost'] = '新しいディスカッションが投稿より作成されました。';
$string['pruneheading'] = '投稿を分割して新しいディスカッションに移動する';
$string['qandareactforum'] = 'Q&AReactフォーラム';
$string['qandanotify'] = 'これはQ&AReactフォーラムです。これらの質問に対する他の人の回答を読むには最初にあなたの回答を投稿する必要があります。';
$string['re'] = 'Re:';
$string['readtherest'] = '残りのトピックを読む';
$string['removeallreactforumtags'] = 'すべてのReactフォーラムタグを削除する';
$string['replies'] = '返信';
$string['repliesmany'] = '現在の返信数: {$a}';
$string['repliesone'] = '現在の返信数: {$a}';
$string['reply'] = '返信';
$string['replyreactforum'] = 'Reactフォーラムに返信する';
$string['reply_handler'] = 'Reactフォーラム投稿にメールで返信します。';
$string['reply_handler_name'] = 'Reactフォーラム投稿に返信する';
$string['replytopostbyemail'] = 'あなたはこのReactフォーラム投稿にメールで返信することができます。';
$string['replytouser'] = '返信にメールアドレスを使用する';
$string['resetdigests'] = 'すべてのユーザのReactフォーラムダイジェストプリファレンスを削除する';
$string['resetreactforums'] = '次のReactフォーラムから投稿を削除する';
$string['resetreactforumsall'] = 'すべての投稿を削除する';
$string['resetsubscriptions'] = 'すべてのReactフォーラムの購読を解除する';
$string['resettrackprefs'] = 'すべてのReactフォーラム未読管理プリファレンスを削除する';
$string['rssarticles'] = '最近の記事のRSS数';
$string['rssarticles_help'] = 'この設定ではRSSフィードに含まれる記事 (ディスカッションおよび投稿) 数を設定します。一般的に5から20の間が適切です。';
$string['rsssubscriberssdiscussions'] = 'ディスカッションのRSSフィード';
$string['rsssubscriberssposts'] = '投稿のRSSフィード';
$string['rsstype'] = 'この活動のRSSフィード';
$string['rsstypedefault'] = 'RSSフィードタイプ';
$string['rsstype_help'] = 'この活動のRSSフィードを有効にするにはフィードに含まれるディスカッションまたは投稿を選択してください。';
$string['search'] = '検索';
$string['search:activity'] = 'Reactフォーラム - 活動情報';
$string['searchdatefrom'] = '投稿がこの日付よりも新しい';
$string['searchdateto'] = '投稿がこの日付よりも古い';
$string['searchreactforumintro'] = '下記のフィールドの少なくとも1つに検索語句を入力してください:';
$string['searchreactforums'] = 'Reactフォーラムを検索する';
$string['searchfullwords'] = 'これらの語を完全に含む';
$string['searchnotwords'] = 'これらの語を含まない';
$string['searcholderposts'] = '過去の投稿を検索する ...';
$string['searchphrase'] = 'このフレーズが正確に投稿に含まれる';
$string['search:post'] = 'Reactフォーラム - 投稿';
$string['searchresults'] = '検索結果';
$string['searchsubject'] = 'これらの語が件名に含まれる';
$string['searchtags'] = 'タグ付け';
$string['searchuser'] = 'この名前が投稿者名に合致する';
$string['searchuserid'] = '投稿者のMoodle ID';
$string['searchwhichreactforums'] = '検索するReactフォーラムを選択してください';
$string['searchwords'] = 'これらの語が投稿のどこかに含まれる';
$string['seeallposts'] = 'このユーザによるすべての投稿を表示する';
$string['shortpost'] = '短い投稿';
$string['showsubscribers'] = '現在の購読者を表示/編集する';
$string['singlereactforum'] = 'トピック1件のシンプルなディスカッション';
$string['smallmessage'] = '{$a->user} による {$a->reactforumname} の投稿';
$string['smallmessagedigest'] = 'Reactフォーラムダイジェストには {$a} 件のメッセージが含まれます。';
$string['startedby'] = 'ディスカッション開始';
$string['subject'] = '件名';
$string['subscribe'] = 'このReactフォーラムを購読する';
$string['subscribeall'] = 'このReactフォーラムをすべての人に購読させる';
$string['subscribed'] = '購読';
$string['subscribediscussion'] = 'このディスカッションを購読する';
$string['subscribeenrolledonly'] = '申し訳ございません、登録しているユーザのみReactフォーラム投稿通知を購読することができます。';
$string['subscribenone'] = 'このReactフォーラムのすべての人の購読を解除する';
$string['subscribers'] = '購読者';
$string['subscribersto'] = '「 {$a->name} 」の購読者';
$string['subscriberstowithcount'] = '「 {$a->name} 」 ({$a->count}) の購読者';
$string['subscribestart'] = 'このReactフォーラムの新しい投稿を私にメール通知してください';
$string['subscribestop'] = 'このReactフォーラムの新しい投稿を私にメール通知しないでください';
$string['subscription'] = '購読';
$string['subscriptionandtracking'] = '購読および未読管理';
$string['subscriptionauto'] = '自動購読';
$string['subscriptiondisabled'] = '購読停止';
$string['subscriptionforced'] = '強制購読';
$string['subscription_help'] = 'Reactフォーラムを購読した場合、あなたが新しいReactフォーラム投稿の通知を受信することを意味します。通常、あなたは購読するかどうか選択することができますが、すべての人が通知を受信するよう購読が強制される場合もあります。';
$string['subscriptionmode'] = '購読モード';
$string['subscriptionmode_help'] = '参加者がReactフォーラムを購読する場合、Reactフォーラムの投稿内容のコピーをメール受信することを意味します。

購読モードには以下4つのオプションがあります:

* 任意購読 - 参加者は購読するかどうか選択することができます。
* 強制購読 - すべての人が購読登録され、購読解除することはできません。
* 自動購読 - 最初にすべての人が購読登録されますが、いつでも購読解除することができます。
* 購読停止 - 購読は許可されません。';
$string['subscriptionoptional'] = '任意購読';
$string['subscriptions'] = '購読';
$string['tagarea_reactforum_posts'] = 'Reactフォーラム投稿';
$string['tagsdeleted'] = 'Reactフォーラムタグが削除されました。';
$string['tagtitle'] = '「 {$a} 」タグを表示する';
$string['thisreactforumisthrottled'] = 'このReactフォーラムでは期限内にあなたが投稿できる投稿数を制限しています - 現在 {$a->blockperiod} で {$a->blockafter} 回に設定されています。';
$string['timedhidden'] = '時間制限ステータス: 学生から隠す';
$string['timedposts'] = '時間制限投稿';
$string['timedvisible'] = '時間制限ステータス: すべてのユーザに表示する';
$string['timestartenderror'] = '表示終了日を表示開始日より前にすることはできません。';
$string['trackreactforum'] = '投稿を未読管理する';
$string['tracking'] = '未読管理';
$string['trackingoff'] = 'Off';
$string['trackingon'] = '強制';
$string['trackingoptional'] = '任意';
$string['trackingtype'] = '未読管理';
$string['trackingtype_help'] = '未読管理により新しい投稿がハイライトされることで参加者はまだ閲覧していない投稿を簡単に確認することができます。

「任意」に設定された場合、参加者は管理ブロック内のリンクにより未読管理を有効または無効にすることができます (ユーザは自分のReactフォーラムプリファレンスでReactフォーラム未読管理を有効にする必要があります)。

サイト管理者が「未読管理の強制を許可する」を有効にした場合、さらなるオプション「強制」を使用することができます。これはユーザのReactフォーラムプリファレンスに限らず常に未読管理が有効にされることを意味します。';
$string['trackreadposts_header'] = '未読管理';
$string['unread'] = '未読';
$string['unreadposts'] = '未読の投稿';
$string['unreadpostsnumber'] = '未読件数 {$a}';
$string['unreadpostsone'] = '未読件数 1';
$string['unsubscribe'] = 'このReactフォーラムの購読を解除する';
$string['unsubscribeall'] = 'すべてのReactフォーラムの購読を解除する';
$string['unsubscribeallconfirm'] = '現在、あなたは {$a->reactforums} 件のReactフォーラムおよび {$a->discussions} 件のディスカッションを購読しています。本当にすべてのReactフォーラムおよびディスカッションの購読を解除してReactフォーラム自動購読を無効にしてもよろしいですか?';
$string['unsubscribeallconfirmdiscussions'] = '現在、あなたは {$a->discussions} 件のディスカッションを購読しています。本当にすべてのディスカッションの購読を解除して自動購読を無効にしてもよろしいですか?';
$string['unsubscribeallconfirmreactforums'] = '現在、あなたは {$a->reactforums} 件のReactフォーラムを購読しています。本当にすべてのReactフォーラムの購読を解除して自動購読を無効にしてもよろしいですか?';
$string['unsubscribealldone'] = 'すべてのReactフォーラムの購読が解除されました。まだ、あなたには購読が強制されているReactフォーラムから通知が送信されます。Reactフォーラム通知を管理するにはマイプロファイル設定のメッセージングにアクセスしてください。';
$string['unsubscribeallempty'] = '申し訳ございません、あなたが購読しているReactフォーラムはありません。このサーバからのすべての通知を無効するにはマイプロファイル設定のメッセージングにアクセスしてください。';
$string['unsubscribed'] = '購読を解除しました。';
$string['unsubscribediscussion'] = 'このディスカッションから購読解除する';
$string['unsubscribediscussionlink'] = 'このディスカッションから購読解除する: {$a}';
$string['unsubscribelink'] = 'このReactフォーラムから購読解除する: {$a}';
$string['unsubscribeshort'] = '購読解除';
$string['usermarksread'] = '投稿を手動で既読にする';
$string['viewalldiscussions'] = 'すべてのディスカッションを表示する';
$string['viewthediscussion'] = 'このディスカッションを表示する';
$string['warnafter'] = '警告までの投稿閾値';
$string['warnafter_help'] = '指定された時間内に指定された投稿数以上を投稿した場合、学生に警告が表示されます。この設定では何件の投稿後に警告が表示されるか指定します。ケイパビリティ「mod/reactforum:postwithoutthrottling」が割り当てられたユーザは投稿制限から除外されます。';
$string['warnformorepost'] = '警告! このReactフォーラムには2件以上のディスカッションがあります - 直近のディスカッションを使用します。';
$string['yournewquestion'] = 'あなたの新しい質問';
$string['yournewtopic'] = 'あなたの新しいディスカッショントピック';
$string['yourreply'] = 'あなたの返信';



/** REACTIONS */

$string['reactionstype'] = 'Reaction Buttons Type';
$string['reactionstype_text'] = 'Text';
$string['reactionstype_image'] = 'Image';
$string['reactionstype_discussion'] = 'Decided by discussion owner';
$string['reactionstype_none'] = 'None';
$string['reactionstype_change_confirmation'] = 'All current reaction buttons will be removed. Are you sure that you want to change reaction type?';

$string['reactions_allreplies'] = 'Apply reaction buttons on replies';
$string['reactions_allreplies_help'] = 'If this option is checked, reaction buttons will appear on each topic and every reply as well. Otherwise, they appear on the discussion topic only.';

$string['reactions'] = 'Reaction Buttons';
$string['reactions_add'] = 'Add';
$string['reactions_changeimage'] = 'Change Image';
$string['reactions_selectfile'] = 'Please select new reaction image file';
$string['reactions_cancel'] = 'Cancel';
$string['reactions_delete'] = 'Delete';
$string['reactions_delete_confirmation'] = 'Are you sure that you want to delete this reaction? All its data will be removed. (You can undo this action by not saving discussion edit)';
$string['reactions_reupload'] = 'Reupload';

$string['error'] = 'Unexpected Error';