###  addon module

The official ResellerClub & LogicBoxes Domain Module that syncs with the OrderBox platform and helps you import and manage all your RC/LB domain names and customers from WHMCS


### Features

--- 

- Automatically import and manage all of your ResellerClub account domain names
- Automatically import and sync all your customer accounts from ResellerClub
- Automatic export to ensure all your WHMCS clients are registered on ResellerClub
- Automatic update client information on ResellerClub as soon as a client is added, updated or deleted on WHMCS
- Manage pending RAA domain names at a glance with a new and easy to use interface
- Get a snapshot of pending RAA domain names on the WHMCS admin dashboard
- Error tracking and logging for easy debugging and troubleshooting

### Installation

---

1. Follow [these instructions](https://docs.whmcs.com/ResellerClub#Activation) to configure and activate ResellerClub as
   the domain registrar on WHMCS
2. Run Registrar TLD Sync as per the instructions [mentioned here](https://docs.whmcs.com/Registrar_TLD_Sync)
3. Download and install the Orderbox addon module from the WHMCS admin area as
   per [these instructions](https://docs.whmcs.com/Addon_Modules)
    - All supported crons are auto enabled for importing and synchronizing domain names and clients from ResellerClub on
      WHMCS
    - You may enable/disable these crons from the Orderbox addon module configuration options

> NOTE
>
> In order for any module crons to run, ensure that a cron job is configured and running on your server as
> per [these instructions](https://help.whmcs.com/m/87388/l/1075205)

