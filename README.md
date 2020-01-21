# Readings

This is an aplication for on-line handover of some numeric data from multiple users to one administrator via a server-side database. It also stores the data and allows to access them later.

The application was made as a hobby project but has been used as a work tool in one estonian accounting company during several years. Its demo version can be tried on the address http://vps32651nl.hyperhost.name:8080/readings/.

In order to explain the aim and the business logic of the application the explanations should be given to the tradition of utility fees calculation in Estonia.

## Utility Fees in Estonia

The most people in Estonia live in their own flats. (They have either obtained the flat as a part of the state property during privatisation after the separation from Soviet Union, or, which is often true for junger ones, have bought it with the aid of a bank loan.) Flat owners are by law organized in flat owners associations, or condominiums. In most cases, flat owners of one appartment house form one condominium. The aim of the condominium is to manage a common part of the real estate and handle interaction between flat owners. Each condominium has an electable management board and its chairman.

Some condominiums order services from one of real estate management companies, however most of them prefer to manage the property themself. The condominium in this case is an intermediary between utility service providers (such as water, gas, heat or electricity providers) and flat owners. It also orders services from a chosen accounting company.

The condominium collects payments from flat owners every month for utilities consumed and pays bills of the utility providers. Every flat is equipped with several water consumption readers, gas readers etc. depending on the utilities available in the house. So residents of a flat need to report the monthly flat readings to the management board, and the board reports the house readings to the accounting company. Based on the readings, the accounting company calculates utility fees for each flat and produces monthly utility bills.

The application enables the management board to pass house readings to the accounting company on-line. It also allows to view (read only) and download house readings for previous period and view past readings for any selected flat. The accountant, or administrator, can create periods, lock or unlock periods for editing and download the monthly readings as a text file.

## Collected Readings

The most usual kind of readers are water volume meters. Very typical situation in big cities is that cold and hot water is supplied to a house by magistral pipe. Cold water is consumed directly, while hot water exchanges its heat with a heating system of the house and also heats clean water consumed as hot water in the house. So, each flat has from one to four risers delivering cold and hot water, and consequently from one to four readers. The readers are usually located either in the bath room or in the kitchen, or in both, and can be hot or cold.

Residents of a flat need to report only readings as of the end of a month as readings for the previous month are known. However, this application stores both readings for the beginning and for the end of each month. This allows to handle the situation when a reader is replaced during a month, and the readings has no correlation with the readings for the previous month. When a user enters readings, the readings for the start of the month are automatically filled from the readings for the end of the previous month. They can be, however, edited if necessary.

Some condominiums have a gas supply and thus gas meters. Some ones decide to record the number of real residents during the current month to calculate expenses for rubbish removal or for lift usage. Most of electricity readers report automatically to the supplier, but some condominiums act as an intermediary in this reporting. So, real needs of condominiums can significantly vary. The application allows to configure itself for each condominium displaying only necessary readings (see **Database**, description of **KY** table).

## Access to the Application

Each condominium is represented by one user in the application and can see only readings of their house. The access to the account is protected by a login and a password. As the data are not very sensitive, the password is stored in the database unencrypted. The password is also stored unencrypted in a hidden field in a page and thus transferred to the database with the next request.

The administrator's console, from the other hand, is not protected, but its URL is not exposed to unauthorised users and is not known to them. The name of the file is not evident (live name differs from the one published in this repository) which prevents undesirable visits to the page.

The logins and passwords for the demo version are shown on http://vps32651nl.hyperhost.name:8080/readings/. As the data are not live, the last period created for each condominium is also shown in the table. The last period is open for editing, but does not contain data. The previous periods contain data, but are locked.

## Installing and Setting up the Application

The application was developed in the following environment:

- PHP Version: 7.0
- MySQL version: 10.1.41-MariaDB

In order to install the application, just copy all the files into a subfolder in your `public_html` folder. The access to the database is hard-coded in the `ConnectToDb()` function in `sql-func.php` file, so you will need to edit it according to your credentials.

In order to create a database, run `readings_db.sql` script. It contains the command for creation the `readings` database. If you can create the database only via your server's control pannel, do it and remove the following lines from the script:

```sql
CREATE DATABASE IF NOT EXISTS `readings` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `readings`;
```

The only other setting is done by direct editing of the `KY` table in the database. You need to enter a condominium data there - name, login, password, number of flats and kind of measurements collected by the condominium. The example of a query to create a condominium:

```sql
INSERT INTO `KY`
(`KyCode`, `Name`, `Password`, `Flats`, `Kitchen`, `KitchenHot`, `Bath`, `BathHot`, `Gas`, `Electricity`, `People`, `Risers`)
VALUES
('Code24', 'Condo Name', 'passw', 72, 1, 1, 1, 1, 0, 0, 1, 0);
```

In the rare case when you also need to handle riser readings, you need to fill data into the `Riser` table.

Other data are added via the administrative console and the user pages.

## Database

The database structure is defined in the `readings_db.sql` file.

![Database structure](https://github.com/leobro98/readings/blob/master/readings_db.png)

### KY

The core table in the database (it is the abbreviation of the word "korteriühistu" which means "flat association" in Estonian). It contains basic data on the condominium:

- KyCode: the primary key and the login to the user's account,
- Name: name of the condominium, displayed in the top of user pages,
- Password: user's password in unencrypted form,
- Flats: total count of flats in the condominium, used to check if all data are entered,
- Kitchen: if there is a cold water meter in the kitchen (`1` - true, `0` - false),
- KitchenHot: if there is a hot water meter in the kitchen (`1` - true, `0` - false),
- Bath, BathHot, Gas, Electricity: the same about meters in the bath, gas meter and electricity meter,
- People: if the number of residents during the last month is collected (`1` - true, `0` - false),
- Risers: if the condominium has a separate water meters on each water riser in the house (showing the water volume consumed by all flats connected to the riser together).

### Period

The period is the accounting period, i.e. the month, for which the data are collected.

- PeriodId: autoincremented key unique for each period,
- KyCode: the reference to the KY table, shows for which condominium the period is created,
- Year: the calendar year,
- Month: the calendar month,
- Locked: if locked (value `-1`), data for the period can not be edited; if not locked (value `0`), new readings can be entered or existing can be edited,
- Finished: the period not finished corresponds to the value `0`; setting the value to `-1` a user signals that all readings are entered, and the accountant can handle them (this state is seen in the administrative console).

### HouseReading

The readings of the main (common) water meter in the condiminium.

- KyCode: the reference to the KY table, shows to which condominium the meter belongs,
- PeriodId: the reference to the Period table, shows for which period the readings are recorded,
- Start: the reading at the start of the period,
- End: the reading at the end of the period.

The KyCode field is excessive but is added to make data easier to discover and is tolerable, as the number of records in the table is very little.

### Reading

The most populated table in the database.

- PeriodId: the reference to the Period table, shows for which period the readings are recorded,
- FlatId: flat number,
- ColdKitchStart: reading of the cold water in the kitchen at the start of the period,
- ColdKitchEnd: reading of the cold water in the kitchen at the end of the period,
- HotKitchStart: reading of the hot water in the kitchen at the start of the period,
- HotKitchEnd: reading of the hot water in the kitchen at the end of the period,
- ColdBathStart: reading of the cold water in the bath room at the start of the period,
- ColdBathEnd: reading of the cold water in the bath room at the end of the period,
- HotBathStart: reading of the hot water in the bath room at the start of the period,
- HotBathEnd: reading of the hot water in the bath room at the end of the period,
- GasStart: reading of the gas meter at the start of the period,
- GasEnd: reading of the gas meter at the end of the period,
- ElectrStart: reading of the electricity meter at the start of the period,
- ElectrEnd: reading of the electricity meter at the end of the period,
- People: number of residents in the flat during the period.

Here the FlatId field is not a reference as there is no table listing flat numbers, alternatively together with their identifiers. Creation of such a table was decided as impractical. Instead, the users are expected to enter flat numbers correctly. This is backed by the unique index on combination of PeriodId and FlatId, which does not allow to enter the same flat twice in the same period. The practice shows that wrong flat numbers are not the issue.

### Riser

As risers have their names (according to the flat numbers connected to them) the table listing all risers is created. This allows to show user a form where all risers of a house are already listed.

- RiserId: riser's identifier,
- KyCode: the reference to the KY table, shows in which condominium the riser exists,
- Name: the name of the riser.

### RiserReading

The table to record readings of water meters on risers.

- PeriodId: the reference to the Period table, shows for which period the readings are recorded,
- RiserId: the reference to the Riser table, identifies the riser,
- Start: reading at the start of the period,
- End: reading at the end of the period.

## Architecture and Structure

There is no architecture in the application. The UI code, business logic and data queries are mixed in the same file, though each file corresponds to a separate UI page. From one hand, this is due to little experience in PHP. From the other hand, it was known from the very beginning that the application will be small, the requirements will not change often and the maintenance will be done by the author. Therefore the most of efforts were put to the usability of the application.

Currently, the absolute minimum of clicks and scrolling is required to perform the tasks with the UI. The initial focus on a page is set to the relevant field, and the page is scrolled to make the field visible. User doesn't need to enter the readings for the period start as it is done automatically. User can move from field to field with the Tab key, and the form is submitted with the Enter key. The application offers the next available flat number. If the numbers go with gaps between them, it offers the number from the first gap. The same form is used both for entering a new readings and for editing of existing ones - if the flat is already saved in this period, the entered readings will overwrite existing ones.

### User Pages

#### index.php

It is a default page displayed when user requests just the folder with the readings application. The page redirects immediately to the `login.php`.

#### login.php

It is the login form. If a user enters URL of some user page in a browser address line, and the login together with the password are not sent in the request body, the user is redirected to this page. The login is `KyCode` from the `KY` table. The checked login and password are returned with the next page as hidden fields in a form. They are sent to the server at the next request, so any subsequent page is available without any time limitations. The next page - `period.php`

#### period.php

Here user can choose a period (year and month) to view. The previous period is filled in the form by default as the reporting is usually done for the previous period. The usual next page is `readings.php`, there lead the default button "Open readings". If the reporting for risers is also set up for the condominium, there is also a button "Open risers" to open `risers.php`.

Here is also a form making a request for `flat.php` when a flat number is entered.

#### readings.php

The page displays readings saved for the period and allows to download them into `*.csv` file using two links on the top. There is also a button on the top of the page allowing to return to the previuos page.

If the period is not locked for editing, the form for one flat is also shown in the bottom. The form is pre-filled with the number of the next flat and start readings for the period. User can save entered data with Save button or by pressing the Enter key. If user wants to enter data for a different flat, they can type the flat number and pre-fill the form for that flat clicking on the link "Fill data" to the right.

If the period is not locked there is also a picture button (scissors) to the right of the table appearing when a row is hovered. The button deletes the saved data for the selected flat. The warning is not shown as the data can be easily re-entered.

There is also a "Data ready" button in the bottom of the page which appears if it was not pressed for the period before. Pressing on the button reports to the accountant (administrator of the application collecting the data) that all data for the period were entered by the user and can be accepted. Pressing the button sets the `Finished` field in the `Period` table to `-1`. After that the button disappears from the page.

#### risers.php

The page can be displayed only for a condominium with riser meters configured in the `KY` table. There is a button on the top of the page allowing to return to the previuos page.

As names of risers are known, and it is not practical to expect from a user to remember them, a different approach is used on this page. The fields for entering readings are displayed for each riser at once. Pressing Save button (or Enter key) saves only risers for which the end reading is entered. Editing fields (text boxes) are not displayed for saved risers. They can be made editable again by pressing a picture button (edit) to the right of the saved riser.

Below the page are start and end reading for the main water meter of the house. They follow the same concept.

#### flat.php

The page displays readings for the specified flat during the last two years. This is simetimes needed to evaluate suspicious readings (usually too big) reported by the flat of calculate supposed readings to be entered if the flat have not reported them. There is a button on the top of the page allowing to return to the previuos page.

#### readings-down.php

The file responsible for downloading a `*.csv` file with flat readings when user clicks on Download links on the top of the `readings.php` page.

#### risers-down.php

The file responsible for downloading a `*.csv` file with riser readings when user clicks on Download links on the top of the `risers.php` page.

### Administrative pages

#### admin.php

The name of this file in the production environment should be another to be not guessed easily. This is a program file responsible for actually two different pages.

The first page lists all registered condominiums with their login/code, name and some data for the previous period. These are fresh data that an accountant needs to decide the actions possible and needed for each condominium. The data are:

- Locked: if the previous month locked,
- Flats: total count of flats in the condominium,
- Entered `<previous month number>`: count of flats with readings entered for the previous month,
- Errors: count of errors in readings for the previous month (the error is a pair of readings giving negative consumption),
- Ready: the user has reported that data for the previous month are ready (pressed the "Data ready" button on the readings page).

When the accountant clicks on a link with one of the condominiums code/name, the second page appears. It shows 12 last periods for the condominium with data about the locking status and count of flats entered. At the bottom of the table there are input fields that allow either to create a new period (if one did not exist) or edit the existing one. The current month is already pre-filled. Editing of the existing period is just locking or unlocking it.

If a period is not locked an error count and a link for readings downloading (into a `*.csv` file) are also displayed.

### Regular Workflow

Normally the application is used the following way.

1. A month finishes. All readings should be already reported to the management board. One of the board enters readings into the application.
2. When all readings of the condominium are entered, the user presses the "Data ready" button on the readings page.
3. Accountant checks administrative page and sees that all data of the condominium for the last month are entered.
4. Accountant dowloads the readings.
5. Accountant locks the previous month.
6. Accountant creates a new period for the current month.
