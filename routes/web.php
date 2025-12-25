<?php

use App\Http\Controllers\AuctionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BlacksmithController;
use App\Http\Controllers\ClanController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\BackpackController;
use App\Http\Controllers\FightController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\InterfaceController;
use App\Http\Controllers\MagicSkillController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\MonsterController;
use App\Http\Controllers\NpcController;
use App\Http\Controllers\PremiumShopController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;


Route::get('/dd',   [HomeController::class, 'gebug'])->name('gebug');
Route::get('/map',   [HomeController::class, 'map'])->name('map');
Route::get('/map2',   [HomeController::class, 'map2'])->name('map2');
Route::get('/map3',   [HomeController::class, 'map3'])->name('map3');
Route::get('/clear',   [HomeController::class, 'clear'])->name('clear');
Route::get('/login/{id}',   [HomeController::class, 'login'])->name('login');
Route::get('/logout',   [UserController::class, 'logout'])->name('logout');

Route::post('/login',   [LoginController::class, 'login'])->name('login')->withoutMiddleware([VerifyCsrfToken::class]);;
Route::get('/register',   [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register')->withoutMiddleware([VerifyCsrfToken::class]);;
Route::post('/register/check', [RegisterController::class, 'registerCheck'])->name('register.check');

//// ======== Ручная настройка верификации ======== //
//
//// Показ уведомления о необходимости подтвердить email
//Route::get('/email/verify', function () {
//    return view('auth.verify');
//})->middleware('auth')->name('verification.notice');
//
//// Обработка подтверждения email по ссылке
//Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
//    $request->fulfill();
//    return redirect('/home')->with('success', 'Email успешно подтвержден!');
//})->middleware(['auth', 'signed'])->name('verification.verify');
//
//// Повторная отправка письма с подтверждением
//Route::post('/email/verification-notification', function (Request $request) {
//    $request->user()->sendEmailVerificationNotification();
//    return back()->with('resent', true);
//})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');



Route::get('/error', [ErrorController::class, 'index'])->name('error');
Route::get('/chat', [ChatController::class, 'index'])->name('chat');
Route::get('/chat-action', [ChatController::class, 'chatAction'])->name('chat-action');

Route::middleware(['updateLastOnline'])->group(function () {
    Route::post('/character/points-save', [CharacterController::class, 'pointSave'])->name('character.point_save');
    Route::get('/character/points', [CharacterController::class, 'point'])->name('character.point');
    Route::get('/character', [CharacterController::class, 'index'])->name('character');

    Route::post('/magic-skill/update', [MagicSkillController::class, 'updateSkill'])->name('magic_skill.update');
    Route::get('/magic-skill', [MagicSkillController::class, 'index'])->name('magic_skill');

    Route::get('/fight/run-away/{id}', [FightController::class, 'runAway'])->name('fight.run-away');
    Route::get('/fight/attack/monster/{id}', [FightController::class, 'attackMonster'])->name('fight.attack.monster');
    Route::get('/fight/attack/{id}/{monsterId}/{action}', [FightController::class, 'attack'])->name('fight.attack');
    Route::get('/fight/{id}', [FightController::class, 'index'])->name('fight');

    Route::get('/location/move/{direction}', [LocationController::class, 'moveTo'])->name('move-to');
    Route::get('/location', [LocationController::class, 'index'])->name('location');

    Route::get('/backpack', [BackpackController::class, 'index'])->name('backpack');
});

Route::get('/auction/{id}/buyItem/{itemId}', [AuctionController::class, 'buyItem'])->name('auction.buy_item');
Route::match(['GET', 'POST'],'/auction/{id}/my-lot/edit/{slotId}', [AuctionController::class, 'myLotEdit'])->name('auction.my_lot.edit');
Route::get('/auction/{id}/my-lot/cancel/{slotId}', [AuctionController::class, 'myLotCancel'])->name('auction.my_lot.cancel');
Route::get('/auction/{id}/my-lot', [AuctionController::class, 'myLot'])->name('auction.my_lot');
Route::post('/auction/{id}/new-lot/save', [AuctionController::class, 'newLotSave'])->name('auction.new_lot.save');
Route::get('/auction/{id}/new-lot', [AuctionController::class, 'newLot'])->name('auction.new_lot');
Route::get('/auction/{id}', [AuctionController::class, 'index'])->name('auction');

Route::get('/shop/{id}/buy-item/{itemId}', [ShopController::class, 'buyItem'])->name('shop.buy_item');
Route::match(['GET', 'POST'], '/shop/{id}/sell-item', [ShopController::class, 'sellItem'])->name('shop.sell_item');
Route::get('/shop/{id}', [ShopController::class, 'index'])->name('shop');

Route::get('/premium/shop', [PremiumShopController::class, 'index'])->name('premium.shop');

Route::match(['GET', 'POST'], '/warehouse/{id}/take-item', [WarehouseController::class, 'takeItem'])->name('warehouse.take_item');
Route::match(['GET', 'POST'], '/warehouse/{id}', [WarehouseController::class, 'index'])->name('warehouse');

Route::get('/blacksmith/kraft/{id}', [BlacksmithController::class, 'kraftItem'])->name('blacksmith.kraft');
Route::get('/blacksmith/{id}/break', [BlacksmithController::class, 'breakItem'])->name('blacksmith.break');
Route::get('/blacksmith/{id}', [BlacksmithController::class, 'index'])->name('blacksmith');

Route::get('/info/m/{id}', [MonsterController::class, 'info'])->name('info.monster');
Route::get('/info/u/{id}', [UserController::class, 'info'])->name('info.user');
Route::get('/info/npc/{id}', [NpcController::class, 'info'])->name('info.npc');

Route::get('/clan/member', [ClanController::class, 'member'])->name('clan.member');
Route::get('/clan', [ClanController::class, 'index'])->name('clan');

Route::get('/heal/{id}', [HealthController::class, 'index'])->name('heal');

Route::get('/npc/{id}', [NpcController::class, 'index'])->name('npc');

Route::get('/quest/{id}/take', [QuestController::class, 'take'])->name('quest.take');
Route::get('/quest/{id}', [QuestController::class, 'quest'])->name('quest');
Route::get('/quests', [QuestController::class, 'list'])->name('quests');

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

Route::get('/on-map', [InterfaceController::class, 'onMap'])->name('on_map');
Route::get('/menu', [InterfaceController::class, 'menu'])->name('menu');
Route::get('/who',  [InterfaceController::class, 'who'])->name('who');
Route::get('/hero', [InterfaceController::class, 'hero'])->name('hero');
Route::get('/game', [InterfaceController::class, 'game'])->name('game');
Route::get('/main', [InterfaceController::class, 'main'])->name('main');
Route::get('/',     [MainController::class, 'index'])->name('index');
