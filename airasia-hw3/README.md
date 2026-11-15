## Use Case Traceability



| Use case | PHP page or pages | Database table | Test evidence |
| :--- | :--- | :--- | :--- |
| **View a List of Gift Cards** | `card-list.php` | `GIFTCARD` | On the navigation header if you press “Gift Card List” you are taken to card-list.php which shows all of the available gift cards in the database. |
| **View Details of One Gift Card** | `card-details.php` | `GIFTCARD` | When browsing the card-list.php page, if you click on the specific card you are taken to the  card-details.php page with the card_id= of the specific card. |
| **Add a Gift Card** | `card-add.php` | `GIFTCARD` | When on the card-add.php  page, you can enter details for a new card. Once the page is submitted, the gift card is added to the database and shows up on the card-list.php  page |
| **Update a Gift Card** | `card-update.php` | `GIFTCARD` | After submitting the card-update.php page, the gift card selected is updated on the database and the changes are reflected on the card-list.php and card-details.php pages. |
| **Delete a Gift Card** | `card-delete.php` | `GIFTCARD` | After deleting a gift card, the gift card is removed from the database and no longer present on the card-list.php page. |