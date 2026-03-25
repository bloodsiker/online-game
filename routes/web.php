<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BackpackController;
use App\Http\Controllers\BlacksmithController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ClanController;
use App\Http\Controllers\ClanSkillController;
use App\Http\Controllers\ClanWarehouseController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\FightController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotbarController;
use App\Http\Controllers\InterfaceController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MagicSkillController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\MonsterController;
use App\Http\Controllers\NpcController;
use App\Http\Controllers\PremiumShopController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ReputationController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/dd', [HomeController::class, 'gebug'])->name('gebug');
Route::get('/map', [HomeController::class, 'map'])->name('map');
Route::get('/map2', [HomeController::class, 'map2'])->name('map2');
Route::get('/map3', [HomeController::class, 'map3'])->name('map3');
Route::get('/clear', [HomeController::class, 'clear'])->name('clear');
Route::get('/login/{id}', [HomeController::class, 'login'])->name('login');
Route::get('/logout', [UserController::class, 'logout'])->name('logout');

Route::post('/login', [LoginController::class, 'login'])->name('login')->withoutMiddleware([VerifyCsrfToken::class]);
Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register')->withoutMiddleware([VerifyCsrfToken::class]);
Route::post('/register/check', [RegisterController::class, 'registerCheck'])->name('register.check');

// // ======== Ручная настройка верификации ======== //
//
// // Показ уведомления о необходимости подтвердить email
// Route::get('/email/verify', function () {
//    return view('auth.verify');
// })->middleware('auth')->name('verification.notice');
//
// // Обработка подтверждения email по ссылке
// Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//    $request->fulfill();
//    return redirect('/home')->with('success', 'Email успешно подтвержден!');
// })->middleware(['auth', 'signed'])->name('verification.verify');
//
// // Повторная отправка письма с подтверждением
// Route::post('/email/verification-notification', function (Request $request) {
//    $request->user()->sendEmailVerificationNotification();
//    return back()->with('resent', true);
// })->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

Route::get('/error', [ErrorController::class, 'index'])->name('error');

Route::middleware(['updateLastOnline'])->group(function () {
    Route::post('/character/points-save', [CharacterController::class, 'pointSave'])->name('character.point_save');
    Route::get('/character/points', [CharacterController::class, 'point'])->name('character.point');
    Route::get('/character', [CharacterController::class, 'index'])->name('character');

    Route::post('/magic-skill/update', [MagicSkillController::class, 'updateSkill'])->name('magic_skill.update');
    Route::get('/magic-skill', [MagicSkillController::class, 'index'])->name('magic_skill');

    Route::post('/slots/update', [SlotController::class, 'updateSlot'])->name('slots.update');
    Route::get('/slots', [SlotController::class, 'index'])->name('slots');

    Route::get('/hotbar', [HotbarController::class, 'index'])->name('hotbar.index');
    Route::post('/hotbar/set', [HotbarController::class, 'set'])->name('hotbar.set');
    Route::post('/hotbar/use', [HotbarController::class, 'use'])->name('hotbar.use');
    Route::delete('/hotbar/clear/{slot}', [HotbarController::class, 'clear'])->name('hotbar.clear');

    Route::get('/fight/run-away/{id}', [FightController::class, 'runAway'])->name('fight.run-away');
    Route::get('/fight/attack/monster/{id}', [FightController::class, 'attackMonster'])->name('fight.attack.monster');
    Route::get('/fight/attack/{id}/{monsterId}/{action}', [FightController::class, 'attack'])->name('fight.attack');
    Route::get('/fight/{id}', [FightController::class, 'index'])->name('fight');

    Route::get('/location/move/{direction}', [LocationController::class, 'moveTo'])->name('move-to');
    Route::get('/location', [LocationController::class, 'index'])->name('location');

    Route::get('/backpack', [BackpackController::class, 'index'])->name('backpack');

    Route::get('/auction/{id}/buyItem/{itemId}', [AuctionController::class, 'buyItem'])->name('auction.buy_item');
    Route::match(['GET', 'POST'], '/auction/{id}/my-lot/edit/{slotId}', [AuctionController::class, 'myLotEdit'])->name('auction.my_lot.edit');
    Route::get('/auction/{id}/my-lot/cancel/{slotId}', [AuctionController::class, 'myLotCancel'])->name('auction.my_lot.cancel');
    Route::get('/auction/{id}/my-lot', [AuctionController::class, 'myLot'])->name('auction.my_lot');
    Route::post('/auction/{id}/new-lot/save', [AuctionController::class, 'newLotSave'])->name('auction.new_lot.save');
    Route::get('/auction/{id}/new-lot', [AuctionController::class, 'newLot'])->name('auction.new_lot');
    Route::get('/auction/{id}', [AuctionController::class, 'index'])->name('auction');

    Route::get('/shop/{id}/buy-item/{itemId}', [ShopController::class, 'buyItem'])->name('shop.buy_item');
    Route::match(['GET', 'POST'], '/shop/{id}/sell-item', [ShopController::class, 'sellItem'])->name('shop.sell_item');
    Route::get('/shop/{id}', [ShopController::class, 'index'])->name('shop');

    Route::post('/exchange/{id}/apply', [ExchangeController::class, 'apply'])->name('exchange.apply');
    Route::get('/exchange/{id}', [ExchangeController::class, 'index'])->name('exchange');

    Route::match(['GET', 'POST'], '/warehouse/{id}/take-item', [WarehouseController::class, 'takeItem'])->name('warehouse.take_item');
    Route::match(['GET', 'POST'], '/warehouse/{id}', [WarehouseController::class, 'index'])->name('warehouse');

    Route::get('/premium/shop', [PremiumShopController::class, 'index'])->name('premium.shop');
    Route::post('/premium/buy', [PremiumShopController::class, 'buy'])->name('premium.buy');
    Route::post('/premium/add-cart', [PremiumShopController::class, 'addCart'])->name('premium.add_cart');
    Route::get('/premium/delete-cart/{id}', [PremiumShopController::class, 'deleteCart'])->name('premium.delete_cart');
    Route::get('/premium/clear-cart', [PremiumShopController::class, 'clearCart'])->name('premium.clear_cart');

    Route::get('/blacksmith/kraft/{id}', [BlacksmithController::class, 'kraftItem'])->name('blacksmith.kraft');
    Route::get('/blacksmith/{id}/break', [BlacksmithController::class, 'breakItem'])->name('blacksmith.break');
    Route::get('/blacksmith/{id}', [BlacksmithController::class, 'index'])->name('blacksmith');
});

Route::get('/info/m/{id}', [MonsterController::class, 'info'])->name('info.monster');
Route::get('/info/u/{id}', [UserController::class, 'info'])->name('info.user');
Route::get('/info/npc/{id}', [NpcController::class, 'info'])->name('info.npc');

Route::get('/clan/member', [ClanController::class, 'member'])->name('clan.member');
Route::post('/clan/member/save-roles', [ClanController::class, 'saveMemberRoles'])->name('clan.member.save-roles');
Route::post('/clan/member/leave', [ClanController::class, 'leaveClan'])->name('clan.member.leave');
Route::delete('/clan/member/{target}', [ClanController::class, 'kickMember'])->name('clan.member.kick');
Route::get('/clan/role', [ClanController::class, 'role'])->name('clan.role');
Route::post('/clan/role/add', [ClanController::class, 'addRole'])->name('clan.role.add');
Route::post('/clan/role/save', [ClanController::class, 'saveRoles'])->name('clan.role.save');
Route::delete('/clan/role/{role}', [ClanController::class, 'deleteRole'])->name('clan.role.delete');
Route::post('/clan/invite', [ClanController::class, 'invite'])->name('clan.invite');
Route::get('/clan/request/{joinRequest}', [ClanController::class, 'cancelRequest'])->name('clan.request.cancel');
Route::get('/clan/information', [ClanController::class, 'information'])->name('clan.information');
Route::get('/clan/logs', [ClanController::class, 'logs'])->name('clan.logs');
Route::get('/clan/quests', [ClanController::class, 'quests'])->name('clan.quests');
Route::post('/clan/information/description', [ClanController::class, 'saveDescription'])->name('clan.information.save-description');
Route::post('/clan/information/news', [ClanController::class, 'saveNews'])->name('clan.information.save-news');
Route::get('/clan', [ClanController::class, 'index'])->name('clan');
Route::post('/clan', [ClanController::class, 'store'])->name('clan.store');
Route::get('/clan/skills', [ClanSkillController::class, 'index'])->name('clan.skills');
Route::post('/clan/skills/{id}/learn', [ClanSkillController::class, 'learn'])->name('clan.skills.learn');

Route::match(['get', 'post'], '/clan-warehouse/{id}', [ClanWarehouseController::class, 'put'])->name('clan.warehouse');
Route::match(['get', 'post'], '/clan-warehouse/{id}/take', [ClanWarehouseController::class, 'take'])->name('clan.warehouse.take');
Route::get('/clan-warehouse/{id}/logs', [ClanWarehouseController::class, 'logs'])->name('clan.warehouse.logs');

Route::get('/heal/{id}', [HealthController::class, 'index'])->name('heal');

Route::get('/npc/{id}', [NpcController::class, 'index'])->name('npc');

Route::get('/quest/{id}/take', [QuestController::class, 'take'])->name('quest.take');
Route::get('/quest/{id}/complete', [QuestController::class, 'complete'])->name('quest.complete');
Route::post('/quest/{id}/cancel', [QuestController::class, 'cancelQuest'])->name('quest.cancel');
Route::post('/quest/clan/{id}/cancel', [QuestController::class, 'cancelClanQuest'])->name('quest.clan.cancel');
Route::get('/quest/{id}', [QuestController::class, 'quest'])->name('quest');
Route::get('/quests', [QuestController::class, 'list'])->name('quests');

Route::get('/reputations', [ReputationController::class, 'list'])->name('reputation.list');
Route::get('/reputation/{id}', [ReputationController::class, 'index'])->name('reputation.index');
Route::post('/reputation/{id}/take', [ReputationController::class, 'take'])->name('reputation.take');
Route::post('/reputation/{id}/decline', [ReputationController::class, 'decline'])->name('reputation.decline');
Route::get('/reputation/{id}/shop', [ReputationController::class, 'shop'])->name('reputation.shop');
Route::post('/reputation/{id}/shop/{itemId}/buy', [ReputationController::class, 'buy'])->name('reputation.buy');

Route::get('/items/info/{id}', [ItemController::class, 'info'])->name('items.info');
Route::get('/items/put-on/{id}', [ItemController::class, 'putOn'])->name('items.put_on');
Route::get('/items/put-off/{id}', [ItemController::class, 'putOff'])->name('items.put_off');
Route::get('/items/pickup/{id}', [ItemController::class, 'pickUp'])->name('items.pick_up');
Route::get('/items/open-chest/{id}', [ItemController::class, 'openChest'])->name('items.open_chest');
Route::get('/items/view-chest/{id}', [ItemController::class, 'viewChest'])->name('items.view_chest');
Route::get('/items/pickup-chest/{chest}/{id}', [ItemController::class, 'pickUpInChest'])->name('items.pickup_chest');
Route::get('/items/hand-over/{id}', [ItemController::class, 'handOver'])->name('items.hand_over');
Route::get('/items/hand-over-to-user/{id}', [ItemController::class, 'handOverToUser'])->name('items.hand_over_to_user');
Route::get('/items/drop/{id}', [ItemController::class, 'dropItem'])->name('items.drop');

Route::get('/take_items', [LocationController::class, 'takeItems'])->name('take_items');

Route::get('/chat', [ChatController::class, 'index'])->name('chat');
Route::get('/chat-text', [ChatController::class, 'chat'])->name('chat.text');
Route::get('/chat-log', [ChatController::class, 'chatLog'])->name('chat.log');
Route::get('/chat-action', [ChatController::class, 'chatAction'])->name('chat.action');
Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
Route::post('/chat/ignore', [ChatController::class, 'addIgnore'])->name('chat.ignore.add');
Route::delete('/chat/ignore/{userId}', [ChatController::class, 'removeIgnore'])->name('chat.ignore.remove');
Route::get('/chat/ignores', [ChatController::class, 'ignoreList'])->name('chat.ignores');

Route::get('/rating', [RatingController::class, 'index'])->name('rating');
Route::get('/rating/search', [RatingController::class, 'search'])->name('rating.search');

Route::get('/on-map', [InterfaceController::class, 'onMap'])->name('on_map');
Route::get('/menu', [InterfaceController::class, 'menu'])->name('menu');
Route::get('/who', [InterfaceController::class, 'who'])->name('who');
Route::get('/hero', [InterfaceController::class, 'hero'])->name('hero');
Route::get('/game', [InterfaceController::class, 'game'])->name('game');
Route::get('/game', [InterfaceController::class, 'game'])->name('game');
Route::get('/interface', [InterfaceController::class, 'interface'])->name('interface');
Route::get('/', [MainController::class, 'index'])->name('index');
