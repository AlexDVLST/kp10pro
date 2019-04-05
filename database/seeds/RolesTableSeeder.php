<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use \Spatie\Permission\Models\Permission;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        // app()['cache']->forget('spatie.permission.cache');

        //Create roles
        if (!Role::whereName('admin')->first()) {
            Role::create(['name' => 'admin']);
        }
        if (!Role::whereName('manager')->first()) {
            Role::create(['name' => 'manager']);
        }
        //Директор
        if (!Role::whereName('user')->first()) {
            Role::create(['name' => 'user']);
        }
        //Менеджер
        if (!Role::whereName('employee')->first()) {
            Role::create(['name' => 'employee']);
        }
        //Руководитель продаж
        if (!Role::whereName('sales-manager')->first()) {
            Role::create(['name' => 'sales-manager']);
        }
        //Куратор КП
        if (!Role::whereName('offer-curator')->first()) {
            Role::create(['name' => 'offer-curator']);
        }
        //Куратор Клиентов
        if (!Role::whereName('client-curator')->first()) {
            Role::create(['name' => 'client-curator']);
        }
        //Редактор Товаров
        if (!Role::whereName('product-editor')->first()) {
            Role::create(['name' => 'product-editor']);
        }
        //Редактор фото
        if (!Role::whereName('photo-editor')->first()) {
            Role::create(['name' => 'photo-editor']);
        }

        //Create translation for roles
        if (!Role::with('translationRelation')->whereName('admin')->first()->translationRelation) {
            Role::whereName('admin')->first()
                ->translationRelation()->create(['translation' => 'Администратор']);
        }
        if (!Role::with('translationRelation')->whereName('manager')->first()->translationRelation) {
            Role::whereName('manager')->first()
                ->translationRelation()->create(['translation' => 'Менеджер-администратор']);
        }
        if (!Role::with('translationRelation')->whereName('user')->first()->translationRelation) {
            Role::whereName('user')->first()
                ->translationRelation()->create(['translation' => 'Директор']);
        }
        if (!Role::with('translationRelation')->whereName('employee')->first()->translationRelation) {
            Role::whereName('employee')->first()
                ->translationRelation()->create(['translation' => 'Менеджер']);
        }
        if (!Role::with('translationRelation')->whereName('sales-manager')->first()->translationRelation) {
            Role::whereName('sales-manager')->first()
                ->translationRelation()->create(['translation' => 'Руководитель продаж']);
        }
        if (!Role::with('translationRelation')->whereName('offer-curator')->first()->translationRelation) {
            Role::whereName('offer-curator')->first()
                ->translationRelation()->create(['translation' => 'Куратор КП']);
        }
        if (!Role::with('translationRelation')->whereName('client-curator')->first()->translationRelation) {
            Role::whereName('client-curator')->first()
                ->translationRelation()->create(['translation' => 'Куратор клиентов']);
        }
        if (!Role::with('translationRelation')->whereName('product-editor')->first()->translationRelation) {
            Role::whereName('product-editor')->first()
                ->translationRelation()->create(['translation' => 'Редактор товаров']);
        }
        if (!Role::with('translationRelation')->whereName('photo-editor')->first()->translationRelation) {
            Role::whereName('photo-editor')->first()
                ->translationRelation()->create(['translation' => 'Редактор фото']);
        }

        //Create permissions
        //Коммерческиое предложение
        if (!Permission::whereName('create offer')->first()) {
            Permission::create(['name' => 'create offer']);
        }
        if (!Permission::whereName('view offer')->first()) {
            Permission::create(['name' => 'view offer']);
        }
        if (!Permission::whereName('view-own offer')->first()) {
            Permission::create(['name' => 'view-own offer']);
        }
        if (!Permission::whereName('edit offer')->first()) {
            Permission::create(['name' => 'edit offer']);
        }
        if (!Permission::whereName('edit-own offer')->first()) {
            Permission::create(['name' => 'edit-own offer']);
        }
        if (!Permission::whereName('delete offer')->first()) {
            Permission::create(['name' => 'delete offer']);
        }
        if (!Permission::whereName('delete-own offer')->first()) {
            Permission::create(['name' => 'delete-own offer']);
        }
        //Товары
        if (!Permission::whereName('create product')->first()) {
            Permission::create(['name' => 'create product']);
        }
        if (!Permission::whereName('view product')->first()) {
            Permission::create(['name' => 'view product']);
        }
        if (!Permission::whereName('edit product')->first()) {
            Permission::create(['name' => 'edit product']);
        }
        if (!Permission::whereName('delete product')->first()) {
            Permission::create(['name' => 'delete product']);
        }
        if (!Permission::whereName('import product')->first()) {
            Permission::create(['name' => 'import product']);
        }
        //Фотографии
        if (!Permission::whereName('view file-manager')->first()) {
            Permission::create(['name' => 'view file-manager']);
        }
        if (!Permission::whereName('edit file-manager')->first()) {
            Permission::create(['name' => 'edit file-manager']);
        }
        //Клиенты
        if (!Permission::whereName('create client')->first()) {
            Permission::create(['name' => 'create client']);
        }
        if (!Permission::whereName('view client')->first()) {
            Permission::create(['name' => 'view client']);
        }
        if (!Permission::whereName('view-own client')->first()) {
            Permission::create(['name' => 'view-own client']);
        }
        if (!Permission::whereName('edit client')->first()) {
            Permission::create(['name' => 'edit client']);
        }
        if (!Permission::whereName('edit-own client')->first()) {
            Permission::create(['name' => 'edit-own client']);
        }
        if (!Permission::whereName('delete client')->first()) {
            Permission::create(['name' => 'delete client']);
        }
        if (!Permission::whereName('delete-own client')->first()) {
            Permission::create(['name' => 'delete-own client']);
        }
        //Настройки
        if (!Permission::whereName('view settings')->first()) {
            Permission::create(['name' => 'view settings']);
        }

        //Assign permission to role
        //Директор
        //КП
        if (!Role::whereName('user')->first()->hasPermissionTo('create offer')) {
            Role::whereName('user')->first()->givePermissionTo('create offer');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('view offer')) {
            Role::whereName('user')->first()->givePermissionTo('view offer');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('view-own offer')) {
            Role::whereName('user')->first()->givePermissionTo('view-own offer');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('edit offer')) {
            Role::whereName('user')->first()->givePermissionTo('edit offer');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('edit-own offer')) {
            Role::whereName('user')->first()->givePermissionTo('edit-own offer');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('delete offer')) {
            Role::whereName('user')->first()->givePermissionTo('delete offer');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('delete-own offer')) {
            Role::whereName('user')->first()->givePermissionTo('delete-own offer');
        }
        //Товары
        if (!Role::whereName('user')->first()->hasPermissionTo('create product')) {
            Role::whereName('user')->first()->givePermissionTo('create product');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('view product')) {
            Role::whereName('user')->first()->givePermissionTo('view product');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('edit product')) {
            Role::whereName('user')->first()->givePermissionTo('edit product');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('delete product')) {
            Role::whereName('user')->first()->givePermissionTo('delete product');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('import product')) {
            Role::whereName('user')->first()->givePermissionTo('import product');
        }
        //Фотографии
        if (!Role::whereName('user')->first()->hasPermissionTo('view file-manager')) {
            Role::whereName('user')->first()->givePermissionTo('view file-manager');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('edit file-manager')) {
            Role::whereName('user')->first()->givePermissionTo('edit file-manager');
        }
        //Клиенты
        if (!Role::whereName('user')->first()->hasPermissionTo('create client')) {
            Role::whereName('user')->first()->givePermissionTo('create client');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('view client')) {
            Role::whereName('user')->first()->givePermissionTo('view client');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('view-own client')) {
            Role::whereName('user')->first()->givePermissionTo('view-own client');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('edit client')) {
            Role::whereName('user')->first()->givePermissionTo('edit client');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('edit-own client')) {
            Role::whereName('user')->first()->givePermissionTo('edit-own client');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('delete client')) {
            Role::whereName('user')->first()->givePermissionTo('delete client');
        }
        if (!Role::whereName('user')->first()->hasPermissionTo('delete-own client')) {
            Role::whereName('user')->first()->givePermissionTo('delete-own client');
        }
        //Настройки
        if (!Role::whereName('user')->first()->hasPermissionTo('view settings')) {
            Role::whereName('user')->first()->givePermissionTo('view settings');
        }

        //Менеджер(сотрудник)
        //КП
        if (!Role::whereName('employee')->first()->hasPermissionTo('create offer')) {
            Role::whereName('employee')->first()->givePermissionTo('create offer');
        }
        if (!Role::whereName('employee')->first()->hasPermissionTo('view-own offer')) {
            Role::whereName('employee')->first()->givePermissionTo('view-own offer');
        }
        if (!Role::whereName('employee')->first()->hasPermissionTo('edit-own offer')) {
            Role::whereName('employee')->first()->givePermissionTo('edit-own offer');
        }
        if (!Role::whereName('employee')->first()->hasPermissionTo('delete-own offer')) {
            Role::whereName('employee')->first()->givePermissionTo('delete-own offer');
        }
        //Товары
        if (!Role::whereName('employee')->first()->hasPermissionTo('view product')) {
            Role::whereName('employee')->first()->givePermissionTo('view product');
        }
        //Фотографии
        if (!Role::whereName('employee')->first()->hasPermissionTo('view file-manager')) {
            Role::whereName('employee')->first()->givePermissionTo('view file-manager');
        }
        //Клиенты
        if (!Role::whereName('employee')->first()->hasPermissionTo('create client')) {
            Role::whereName('employee')->first()->givePermissionTo('create client');
        }
        if (!Role::whereName('employee')->first()->hasPermissionTo('view-own client')) {
            Role::whereName('employee')->first()->givePermissionTo('view-own client');
        }
        if (!Role::whereName('employee')->first()->hasPermissionTo('edit-own client')) {
            Role::whereName('employee')->first()->givePermissionTo('edit-own client');
        }
        if (!Role::whereName('employee')->first()->hasPermissionTo('delete-own client')) {
            Role::whereName('employee')->first()->givePermissionTo('delete-own client');
        }
        //Настройки
        //No

        //Руководитель продаж
        //КП
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('create offer')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('create offer');
        }
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('view offer')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('view offer');
        }
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('edit offer')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('edit offer');
        }
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('delete offer')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('delete offer');
        }
        //Товары
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('view product')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('view product');
        }
        //Фотографии
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('view file-manager')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('view file-manager');
        }
        //Клиенты
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('create client')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('create client');
        }
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('view client')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('view client');
        }
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('edit client')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('edit client');
        }
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('delete client')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('delete client');
        }
        //Настройки
        //No

        //Куратор КП
        //КП
        if (!Role::whereName('offer-curator')->first()->hasPermissionTo('view offer')) {
            Role::whereName('offer-curator')->first()->givePermissionTo('view offer');
        }
        //Товары
        //No
        //Фотографии
        //No
        //Клиенты
        //No
        //Настройки
        //No

        //Куратор Клиентов
        //КП
        //No
        //Товары
        //No
        //Фотографии
        //No
        //Клиенты
        if (!Role::whereName('client-curator')->first()->hasPermissionTo('view client')) {
            Role::whereName('client-curator')->first()->givePermissionTo('view client');
        }
        //Настройки
        //No

        //Редактор Товаров
        //КП
        //No
        //Товары
        if (!Role::whereName('product-editor')->first()->hasPermissionTo('create product')) {
            Role::whereName('product-editor')->first()->givePermissionTo('create product');
        }
        if (!Role::whereName('product-editor')->first()->hasPermissionTo('view product')) {
            Role::whereName('product-editor')->first()->givePermissionTo('view product');
        }
        if (!Role::whereName('product-editor')->first()->hasPermissionTo('edit product')) {
            Role::whereName('product-editor')->first()->givePermissionTo('edit product');
        }
        if (!Role::whereName('product-editor')->first()->hasPermissionTo('delete product')) {
            Role::whereName('product-editor')->first()->givePermissionTo('delete product');
        }
        if (!Role::whereName('product-editor')->first()->hasPermissionTo('import product')) {
            Role::whereName('product-editor')->first()->givePermissionTo('import product');
        }
        //Фотографии
        //No
        //Клиенты
        //No
        //Настройки
        //No

        //Редактор Фото
        //КП
        //No
        //Товары
        //No
        //Фотографии
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('edit file-manager')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('edit file-manager');
        }
        if (!Role::whereName('sales-manager')->first()->hasPermissionTo('view file-manager')) {
            Role::whereName('sales-manager')->first()->givePermissionTo('view file-manager');
        }
        //Клиенты
        //No
        //Настройки
        //No

        //Admin permissions
        if (!Permission::whereName('admin offer')->first()) {
            Permission::create(['name' => 'admin offer']);
        }
        if (!Permission::whereName('admin help')->first()) {
            Permission::create(['name' => 'admin help']);
        }
    }
}
