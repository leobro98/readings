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

Some condominiums have a gas supply and thus gas meters. Some ones decide to record the number of real residents during the current month to calculate expenses for rubbish removal or for lift usage. Most of electricity readers report automatically to the supplier, but some condominiums act as an intermediary in this reporting. So, real needs of condominiums can significantly vary. The application allows to configure itself for each condominium displaying only necessary readings (see description of KY table).

## Access to the Application

Each condominium is represented by one user in the application and can see only readings of their house. The access to the account is protected by a login and a password. As the data are not very sensitive, the password is stored in the database unencrypted. The password is also stored unencrypted in a hidden field in a page and thus transferred to the database with the next request.

The administrator's console, from the other hand, is not protected, but its URL is not exposed to unauthorised users. The name of the file is not evident (live name differs from the one published in this repository).

The logins and passwords for the demo version are shown on http://vps32651nl.hyperhost.name:8080/readings/. As the data are not live, the last period created for each condominium is also shown in the table. The last period is open for editing, but does not contain data. The previous periods contain data, but are locked.

## Installing and Setting up the Application

PHP Version: 7.0
MySQL version: 10.1.41-MariaDB

The access to the database is hard-coded in the `ConnectToDb()` function in `sql-func.php` file.

The only other setting is done by direct editing of `KY` table in the database. You need to enter a condominium data there - name, login, password, number of flats and kind of measurements collected by the condominium.

## Database

The database structure is defined in the `readings_db.sql` file.

![Database structure](https://github.com/leobro98/readings/readings_db.png)

