if [ -d "$1" ]; then
	echo "Project already exists. Exit."
	exit;
fi

echo "Creating project $1..."
echo "Creating directory"
mkdir $1
echo "Copying files"
cp CodeIgniter-2.2.6/. $1 -R 
cp magico/. $1 -R
rm -r $1/magicopkg
rm -r $1/application/core
rm -fr $1/.git
rm $1/createMagicoProject.sh
rm -r $1/user_guide
echo "Creating symlinks"
ln -s $PWD/magico/magicopkg $1/magicopkg
ln -s $PWD/magico/application/core $1/application/core
echo "Renaming files"
mv $1/css/newWebsite.css $1/css/$1.css
echo "Changing permissions"
chmod 777 $1/uploads
chmod 777 $1/uploads/thumbs
echo "Creating database"
mysqladmin -u root --password=asdasd create $1;
mysql -u root --password=asdasd $1 < $1/MagicoDefaultDB.sql 
echo "Search and replace"
sed -i "s/fooProject/$1/g" $1/.htaccess
sed -i "s/fooProject/$1/g" $1/application/config/database.php
sed -i "s/fooProject/$1/g" $1/application/config/config.php
sed -i "s/fooProject/$1/g" $1/application/views/master_page.php
sed -i "s/fooProject/$1/g" $1/application/controllers/MasterController.php
echo "Done :)"

