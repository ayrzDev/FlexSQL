# FlexSQL - Class
Bu PHP sınıfı, güvenli bir şekilde SQL sorgularını yürütmek için bir arayüz sağlar. Ayrıca, genel kullanıma yönelik yaygın SQL işlemlerini kolaylaştırmak için bazı yardımcı yöntemler de içerir.
## Setup
Bu sınıfın kullanımı için, öncelikle PHP'de PDO eklentisini yüklemeniz gerekir. Daha sonra, SQL sınıfını projenize dahil edebilirsiniz. Örneğin:

```php
require_once 'SQL.php';

$sql = new SQL('localhost', 'mydatabase', 'myusername', 'mypassword');
```

## Basic Usage
SQL sınıfı, PDO'nun özelliklerine doğrudan erişebilmenizi sağlar. Sınıfın temel kullanımı, bir SQL sorgusu oluşturmak, sorguya bağlı parametreler belirtmek ve ardından sorguyu yürütmek için 'query()' yöntemini kullanmaktır.

```php
$sql->query('SELECT * FROM users WHERE username = ?', ['ayrz.dev']);
$result = $sql->fetchAll();
```

## Helper Methods
SQL sınıfı, yaygın SQL işlemlerini gerçekleştirmek için bazı yardımcı yöntemler de sağlar. Bu yöntemler, sorgu oluşturma sürecini basitleştirir ve okunabilirliği arttırır.

### `select()` 
`select()` yöntemi, bir `SELECT` sorgusu oluşturmanıza yardımcı olur. İsteğe bağlı olarak, tablo adı, sütun adları ve bir WHERE ifadesi belirtebilirsiniz.

```php
$sql->select('users', ['id', 'username'], 'age > ?', [18]);
$result = $sql->fetchAll();
```

### `insert()`

`insert()` yöntemi, bir `INSERT` sorgusu oluşturmanıza yardımcı olur. İsteğe bağlı olarak, tablo adı ve eklenecek verileri belirtebilirsiniz. Bu yöntem, eklenen kaydın ID'sini döndürür.

```php
$data = ['username' => 'john.doe', 'email' => 'john.doe@example.com'];
$id = $sql->insert('users', $data);
```

### `update()`

`update()` yöntemi, bir `UPDATE` sorgusu oluşturmanıza yardımcı olur. İsteğe bağlı olarak, tablo adı, güncellenecek veriler ve bir WHERE ifadesi belirtebilirsiniz. Bu yöntem, güncellenen kayıt sayısını döndürür.

```php
$data = ['email' => 'john.doe@example.com'];
$where = 'username = ?';
$params = ['john.doe'];
$rows = $sql->update('users', $data, $where, $params);
```

### `delete()`

```php
$sql->delete('mytable');
$sql->delete('mytable', 'id = ?', [1]);
```
