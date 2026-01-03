const allProducts = [
    { id: 1, name: 'Beanie Woolen Hat', price: 1050, image: 'images/winter hat girl.png', category: 'Women', subcategory: 'Accessories', description: 'Stay warm and fashionable with this cozy beanie woolen hat. Perfect for cold winter days, this soft knit cap provides excellent insulation while adding a trendy touch to your outfit. Available in classic colors to match any style.', sizes: ['One Size'], colors: ['White', 'Beige', 'Gray'] },
    { id: 2, name: 'Women\'s Hooded Trench Coat', price: 5000, image: 'images/red sweater for women.png', category: 'Women', subcategory: 'Outerwear', description: 'Elevate your winter wardrobe with this elegant hooded trench coat. Featuring a stylish red design with a protective hood, this coat combines fashion and functionality. Perfect for chilly weather, it offers warmth without sacrificing sophistication.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Red', 'Black'] },
    { id: 3, name: 'Hat Scarf Set', price: 1520, image: 'images/winter hat black boy.png', category: 'Men', subcategory: 'Accessories', description: 'Complete your winter look with this perfectly matched hat and scarf set. Designed for maximum comfort and warmth, this coordinated ensemble keeps you protected from cold winds. The classic design ensures you stay stylish throughout the season.', sizes: ['One Size'], colors: ['Black', 'Gray'] },
    { id: 4, name: 'Cable-Knit Sweater', price: 5000, image: 'images/red sweater for boys.png', category: 'Men', subcategory: 'Sweaters', description: 'Experience timeless style with this classic cable-knit sweater. The intricate knit pattern adds texture and visual interest while providing exceptional warmth. This versatile piece works perfectly for casual outings or layering on colder days.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Red', 'Navy'] },
    { id: 5, name: 'Pom Pom Hat and Gloves', price: 1500, image: 'images/winter hat red black kids.png', category: 'Kids', subcategory: 'Accessories', description: 'Keep your little ones warm and adorable with this cute pom pom hat and gloves set. The playful design features a fluffy pom pom on top while the matching gloves ensure tiny hands stay toasty. Perfect for building snowmen and winter adventures.', sizes: ['2-4Y', '5-7Y'], colors: ['Red/Black Plaid'] },
    { id: 6, name: 'Striped Oversized Sweater', price: 4700, image: 'images/white and baige sweater girl.png', category: 'Women', subcategory: 'Sweaters', description: 'Embrace cozy comfort with this trendy oversized striped sweater. The relaxed fit and soft fabric make it perfect for lounging or casual outings. The elegant white and beige stripes create a timeless pattern that pairs beautifully with any bottoms.', sizes: ['S', 'M', 'L'], colors: ['White/Beige Stripe'] },
    { id: 7, name: 'Pom Pom Hat', price: 950, image: 'images/winter hat red girl.png', category: 'Women', subcategory: 'Accessories', description: 'Add a playful touch to your winter wardrobe with this charming pom pom hat. The fluffy pom pom on top adds character while the snug fit keeps you warm during cold days. This fashionable accessory is perfect for everyday wear or outdoor activities.', sizes: ['One Size'], colors: ['Black', 'Navy', 'Gray'] },
    { id: 8, name: 'Varsity Jacket (Unisex)', price: 5500, image: 'images/black white varsity jacket.png', category: 'Men', subcategory: 'Casual Wear', description: 'Channel classic American style with this iconic varsity jacket. Featuring the timeless black and white colorway with ribbed cuffs and collar, this unisex piece works for both men and women. Perfect for creating a sporty, casual look that never goes out of style.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Black/White', 'Navy/White'] },
    { id: 9, name: 'Capreze Men Winter Jacket', price: 7500, image: 'images/black boy jacket.png', category: 'Men', subcategory: 'Outerwear', description: 'Brave the coldest weather with this premium Capreze winter jacket. Built with durable materials and superior insulation, this jacket keeps you warm in extreme conditions. The sleek black design offers versatility while the functional features ensure maximum protection from the elements.', sizes: ['M', 'L', 'XL', 'XXL'], colors: ['Black'] },
    { id: 10, name: 'Blue Denim Jacket', price: 4500, image: 'images/Denim Jacket for boys.png', category: 'Men', subcategory: 'Casual Wear', description: 'A wardrobe essential for every man, this classic denim jacket never goes out of style. The versatile blue wash pairs effortlessly with any outfit, from casual jeans to chinos. Durable construction and timeless design make this a piece you will reach for season after season.', sizes: ['M', 'L', 'XL', 'XXL'], colors: ['Light Wash', 'Dark Wash', 'Black'] },
    { id: 11, name: 'Raglan Sweater', price: 4400, image: 'images/skin sweater for boys.png', category: 'Men', subcategory: 'Sweaters', description: 'Experience unmatched comfort with this soft raglan sweater. The distinctive raglan sleeves provide ease of movement while the cozy fabric keeps you warm. Perfect for layering or wearing solo, this sweater is ideal for everyday comfort and casual style.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Beige', 'Brown', 'Gray'] },
    { id: 12, name: 'Green Varsity Jacket', price: 4500, image: 'images/Green white varsity jacket for kids.png', category: 'Kids', subcategory: 'Casual Wear', description: 'Let your kids stand out with this vibrant green varsity jacket. The classic varsity style with contrasting white sleeves creates a sporty, energetic look. Perfect for school, playtime, or family outings, this jacket keeps kids warm while looking cool.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Green/White'] },
    { id: 13, name: 'Oversized vintage denim varsity', price: 6400, image: 'images/Blue white varsity jacket for girls.png', category: 'Women', subcategory: 'Casual Wear', description: 'Make a bold fashion statement with this oversized vintage denim varsity jacket. The relaxed fit and retro-inspired design create an effortlessly cool aesthetic. The blue denim body with white sleeves offers a fresh take on the classic varsity style, perfect for casual outings.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Blue/White'] },
    { id: 14, name: 'Varsity Embroidered Jacket', price: 4900, image: 'images/red white Varsity Jacket for kids.png', category: 'Kids', subcategory: 'Casual Wear', description: 'This stylish varsity jacket features beautiful embroidered details that add personality and charm. The vibrant red and white color combination makes it eye-catching and fun. Perfect for active kids who want to look their best while playing or going to school.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Red/White'] },
    { id: 15, name: 'Loose Fit Black Cargo Skate Pant', price: 3200, image: 'images/Black cargo pant for boys.png', category: 'Men', subcategory: 'Casual Wear', description: 'These comfortable loose fit cargo pants combine style with functionality. Multiple pockets provide practical storage while the relaxed fit ensures freedom of movement. The versatile black color and skate-inspired design make these pants perfect for streetwear enthusiasts and everyday casual wear.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Black'] },
    { id: 16, name: 'Nature Varsity Jacket', price: 7400, image: 'images/musturd and white Varsity Jacket for men.png', category: 'Men', subcategory: 'Casual Wear', description: 'Stand out from the crowd with this premium nature varsity jacket in a unique mustard and white design. The bold color combination creates a fresh, modern look while maintaining the classic varsity aesthetic. High-quality materials and excellent craftsmanship ensure durability and long-lasting style.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Mustard/White'] },
    { id: 17, name: 'Military Hooded Jacket', price: 3500, image: 'images/green jacket.png', category: 'Women', subcategory: 'Outerwear', description: 'Embrace utility-inspired fashion with this durable military hooded jacket. The rugged green design and functional features make it perfect for outdoor adventures and urban exploration. Multiple pockets and a protective hood ensure you are prepared for any weather while looking effortlessly stylish.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Green'] },
    { id: 18, name: 'Puffer Jacket', price: 4000, image: 'images/black jacket for boys kids.png', category: 'Kids', subcategory: 'Outerwear', description: 'Keep your little ones warm and cozy with this insulated puffer jacket. The quilted design provides excellent warmth retention while the lightweight construction ensures comfort. Perfect for cold winter days, this jacket is both practical and stylish for active children.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Black'] },
    { id: 19, name: 'Windproof Coat Jacket', price: 2600, image: 'images/skin color sweater for kids.png', category: 'Kids', subcategory: 'Outerwear', description: 'Protect your children from harsh winds with this specialized windproof coat jacket. The beige color offers versatility while the wind-resistant fabric keeps them comfortable during breezy days. Lightweight yet warm, this jacket is perfect for transitional weather and outdoor play.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Beige'] },
    { id: 20, name: 'Beanie Warm Hat', price: 720, image: 'images/winter hat white boy.png', category: 'Men', subcategory: 'Accessories', description: 'Simple yet effective, this cozy beanie hat provides essential warmth during cold winter months. The classic white color coordinates with any outfit while the snug fit ensures heat retention. An affordable and practical accessory that every winter wardrobe needs for comfortable outdoor activities.', sizes: ['One Size'], colors: ['White'] },
    { id: 21, name: 'Fawn Mock Neck Puffer Jacket', price: 5500, image: 'images/skin jacket for boys.png', category: 'Men', subcategory: 'Outerwear', description: 'Elevate your winter style with this sophisticated fawn mock neck puffer jacket. The elegant neutral tone offers versatility while the mock neck design provides extra warmth and a modern silhouette. Premium insulation ensures you stay warm without the bulk of traditional winter coats.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Fawn'] },
    { id: 22, name: 'Sherpa Collar Jacket', price: 5560, image: 'images/red jackets for boys.png', category: 'Men', subcategory: 'Outerwear', description: 'Experience ultimate comfort with this premium sherpa collar jacket. The plush sherpa lining on the collar provides exceptional warmth and a luxurious feel. The bold red color makes a confident statement while the quality construction ensures this jacket will be your winter favorite for years to come.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Red'] },
    { id: 23, name: 'Mowbeat Baseball Jacket Varsity Style', price: 2400, image: 'images/Orange and white varsity jacket for kids.png', category: 'Kids', subcategory: 'Casual Wear', description: 'Bring energy and fun to your child wardrobe with this vibrant orange and white baseball jacket. The classic varsity styling with modern Mowbeat design creates a perfect balance of tradition and trendiness. Great for young sports enthusiasts or kids who love standout casual wear.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Orange/White'] },
    { id: 24, name: 'Khaki Green Cargo Trouser', price: 3200, image: 'images/Green Cargo pants for boys.png', category: 'Men', subcategory: 'Casual Wear', description: 'These versatile khaki green cargo trousers combine military-inspired style with everyday functionality. Multiple cargo pockets provide ample storage space while the durable fabric withstands daily wear. The earthy green tone adds a rugged outdoor aesthetic to your casual wardrobe.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Khaki Green'] },
    { id: 25, name: 'Cargo Joggers Pant', price: 4400, image: 'images/Gray cargo pant for boys.png', category: 'Men', subcategory: 'Casual Wear', description: 'Merge athleisure comfort with utilitarian style in these innovative cargo joggers. The tapered fit and elasticated ankles provide a modern silhouette while multiple cargo pockets add functionality. Perfect for those who want the comfort of joggers with the practicality of cargo pants.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Gray'] },
    { id: 26, name: 'Crew Neck Sweater', price: 4200, image: 'images/brown sweater boy.png', category: 'Men', subcategory: 'Sweaters', description: 'This timeless crew neck sweater in rich brown is a wardrobe essential every man needs. The classic cut and premium knit fabric ensure both style and comfort. Layer it over a collared shirt for smart-casual occasions or wear it solo for relaxed weekend comfort.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Brown'] },
    { id: 27, name: 'Fur Collar Down Puffer Jacket', price: 6700, image: 'images/black jacket.png', category: 'Women', subcategory: 'Outerwear', description: 'Indulge in luxury warmth with this premium fur collar down puffer jacket. The genuine down insulation provides unmatched warmth for extreme cold conditions. The elegant fur collar adds a touch of sophistication while the sleek black design ensures versatility with any outfit.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Black'] },
    { id: 28, name: 'Walmart Hooded Jacket', price: 4100, image: 'images/black jacket for kids.png', category: 'Kids', subcategory: 'Outerwear', description: 'A reliable and affordable choice for keeping kids warm, this hooded jacket offers excellent value without compromising on quality. The protective hood shields from wind and light rain while the insulated interior keeps children comfortable. Perfect for everyday school wear and outdoor activities.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Black'] },
    { id: 29, name: 'Pocketed Denim Jacket', price: 3900, image: 'images/Black Denim Jacket for boys.png', category: 'Men', subcategory: 'Casual Wear', description: 'Reinvent the classic denim jacket with this stylish black version featuring functional pockets. The timeless denim construction meets modern black wash for a contemporary edge. Multiple pockets add practical storage while the versatile design works with virtually any casual outfit.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Black'] },
    { id: 30, name: 'Varsity Jacket Casual Baseball Outwear', price: 3700, image: 'images/Black and white varsity jacket for kids.png', category: 'Kids', subcategory: 'Casual Wear', description: 'This cool varsity jacket brings authentic baseball style to kids casual wardrobe. The classic black and white color scheme never goes out of style while the comfortable fit allows for active play. Perfect for sports fans or kids who love the timeless varsity look.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['Black/White'] },
    { id: 31, name: 'Striped Knit Zip Collar Pullover Sweater', price: 2300, image: 'images/black and white lining sweater girls.png', category: 'Women', subcategory: 'Sweaters', description: 'Add contemporary flair to your wardrobe with this trendy striped knit pullover featuring a stylish zip collar. The bold black and white stripes create visual interest while the zip detail allows temperature control. Soft knit fabric ensures all-day comfort whether you are working or relaxing.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Black/White'] },
    { id: 32, name: 'French Terry Hoodie', price: 4600, image: 'images/grey hoodie for boys.png', category: 'Men', subcategory: 'Sweaters', description: 'Experience superior comfort with this premium French terry hoodie. The luxurious fabric feels soft against skin while providing excellent warmth. The classic grey color and relaxed fit make it perfect for lounging at home, running errands, or casual outings with friends.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Grey'] },
    { id: 33, name: 'Sand Drop Shoulder Hoodie', price: 6900, image: 'images/grey hoodie for girls.png', category: 'Women', subcategory: 'Sweaters', description: 'Embrace laid-back style with this sand-colored drop shoulder hoodie. The relaxed, oversized fit creates an effortlessly cool aesthetic perfect for modern casual wear. The subtle grey tone and dropped shoulder design make this hoodie a versatile piece for any season.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Grey'] },
    { id: 34, name: 'Pink Butterfly and Stars Printed Hoodie', price: 5500, image: 'images/pink hoodie.png', category: 'Women', subcategory: 'Sweaters', description: 'Express your whimsical side with this charming pink hoodie featuring delicate butterfly and star prints. The feminine design combines comfort with personality, making it perfect for casual outings or cozy days at home. The soft pink color and playful graphics create an enchanting look.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Pink'] },
    { id: 35, name: 'Lilac Basic Fleece Hoodie', price: 7400, image: 'images/purple hoodie for girls.png', category: 'Women', subcategory: 'Sweaters', description: 'Discover cozy luxury with this beautiful lilac basic fleece hoodie. The soft fleece interior provides exceptional warmth while the elegant purple hue adds a sophisticated touch to casual wear. Perfect for those who appreciate quality basics with a pop of color in their wardrobe.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Lilac'] },
    { id: 36, name: 'Plain White Hoodie', price: 7000, image: 'images/white hoodie.png', category: 'Women', subcategory: 'Sweaters', description: 'The ultimate wardrobe essential, this plain white hoodie offers clean, minimalist style that works with everything. Premium fabric ensures lasting quality while the classic design provides timeless appeal. Perfect for layering or wearing solo, this versatile piece is a must-have staple.', sizes: ['S', 'M', 'L', 'XL'], colors: ['White'] },
    { id: 37, name: 'Crew Neck Long Sleeve Hoodie', price: 6800, image: 'images/yellow hoodie for boys.png', category: 'Men', subcategory: 'Sweaters', description: 'Stand out with this vibrant yellow crew neck hoodie that adds energy to any outfit. The comfortable crew neck design and long sleeves make it perfect for layering or solo wear. The bold color choice makes a confident statement while maintaining casual comfort for everyday activities.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Yellow'] },
    { id: 38, name: 'Accolade Crew Neck Sweatshirt', price: 4600, image: 'images/beige sweatshirt for boys.png', category: 'Men', subcategory: 'Sweaters', description: 'This classic Accolade crew neck sweatshirt in neutral beige offers understated style and all-day comfort. The timeless design works seamlessly with any casual wardrobe while the soft fabric ensures you stay cozy. A reliable choice for men who value quality basics and versatile pieces.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Beige'] },
    { id: 39, name: 'Levy Essential Fleece Hoodie', price: 7700, image: 'images/black hoodie for boys.png', category: 'Men', subcategory: 'Sweaters', description: 'Invest in premium comfort with the Levy Essential fleece hoodie. This top-tier hoodie features superior fleece construction for unmatched warmth and softness. The classic black color and quality craftsmanship ensure this becomes your go-to hoodie for years to come.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Black'] },
    { id: 40, name: 'Plain Fleece Full Sleeves Pull Over Sweatshirt', price: 5200, image: 'images/black sweat shirt for men.png', category: 'Men', subcategory: 'Sweaters', description: 'Simple yet effective, this plain fleece pullover sweatshirt delivers comfort and warmth without fuss. The full sleeves and cozy fleece interior make it perfect for cool weather while the classic black color ensures it matches everything. An essential layering piece for every man wardrobe.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Black'] },
    { id: 41, name: 'Oversized Active Sweatshirt', price: 4500, image: 'images/pink sweat shirt for girls.png', category: 'Women', subcategory: 'Sweaters', description: 'Merge athleisure style with cozy comfort in this trendy oversized active sweatshirt. The relaxed fit allows free movement while the soft pink color adds femininity to your active wear. Perfect for workouts, yoga sessions, or simply lounging in style at home.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Pink'] },
    { id: 42, name: 'Line Art Fleece Full Sleeves Sweatshirt', price: 6300, image: 'images/pink sweatshirt.png', category: 'Women', subcategory: 'Sweaters', description: 'Make an artistic statement with this unique line art fleece sweatshirt. The creative design features abstract line artwork on soft pink fabric, combining comfort with contemporary style. Full sleeves and cozy fleece construction ensure warmth while the distinctive graphics showcase your personality.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Pink'] },
    { id: 43, name: 'Block Sweatshirt', price: 5900, image: 'images/pink white grey sweatshirt.png', category: 'Women', subcategory: 'Sweaters', description: 'Express your style with this eye-catching color block sweatshirt featuring pink, white, and grey panels. The geometric color blocking creates a modern, fashion-forward look that stands out. Comfortable construction and trendy design make this perfect for women who love contemporary casual fashion.', sizes: ['S', 'M', 'L', 'XL'], colors: ['Multi-Color'] },
    { id: 44, name: 'Lightweight Sweatshirt', price: 4800, image: 'images/sky blue sweatshirt for boys.png', category: 'Men', subcategory: 'Sweaters', description: 'Perfect for transitional weather, this lightweight sweatshirt in refreshing sky blue offers comfort without overheating. The breathable fabric provides just enough warmth for cool mornings or air-conditioned spaces. The cheerful blue color adds a bright touch to casual outfits throughout the year.', sizes: ['S', 'M', 'L', 'XL', 'XXL'], colors: ['Sky Blue'] },
    { id: 45, name: 'Top Crew Sweatshirt', price: 2300, image: 'images/white blue sweatshirt for kids.png', category: 'Kids', subcategory: 'Sweaters', description: 'Keep your little ones cozy with this adorable top crew sweatshirt in white and blue. The comfortable design allows freedom for play while the charming color combination appeals to kids tastes. Durable construction withstands active wear making it perfect for school and everyday adventures.', sizes: ['2-3Y', '4-5Y', '6-7Y', '8-9Y'], colors: ['White/Blue'] },
    { id: 46, name: 'Oversized Sweatshirt', price: 4200, image: 'images/white sweatshirt for women.png', category: 'Women', subcategory: 'Sweaters', description: 'Embrace effortless style with this comfortable oversized white sweatshirt. The deliberately roomy fit creates a relaxed, contemporary silhouette perfect for modern casual dressing. Pair with leggings or jeans for an easy, chic look that prioritizes both comfort and fashion.', sizes: ['S', 'M', 'L', 'XL'], colors: ['White'] },
    
];

let filteredProducts = [...allProducts];

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');
    const categoryParam = urlParams.get('category');

    let initialProducts = [...allProducts];

    if (categoryParam) {
        const filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(b => b.classList.remove('active'));
        const targetBtn = document.querySelector(`.filter-btn[data-category="${categoryParam}"]`);
        if (targetBtn) targetBtn.classList.add('active');

        if (categoryParam === 'all') {
            initialProducts = [...allProducts];
        } else {
            initialProducts = allProducts.filter(p => p.category === categoryParam || p.subcategory === categoryParam);
        }
    }

    if (searchParam) {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.value = searchParam;
        }
        performSearch(searchParam, initialProducts);
    } else {
        displayProducts(initialProducts);
    }
    
    const dropdownSelected = document.getElementById('dropdownSelected');
    const dropdownOptions = document.getElementById('dropdownOptions');
    const dropdownOptionElements = document.querySelectorAll('.dropdown-option');
    
    dropdownSelected.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownOptions.classList.toggle('show');
    });
    
    dropdownOptionElements.forEach(option => {
        option.addEventListener('click', function(e) {
            e.stopPropagation();
            
            dropdownSelected.textContent = this.textContent;
            
            dropdownOptionElements.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            dropdownOptions.classList.remove('show');
            
            const sortValue = this.getAttribute('data-value');
            sortProducts(sortValue);
        });
    });
    
    document.addEventListener('click', function() {
        dropdownOptions.classList.remove('show');
    });
    
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const category = this.getAttribute('data-category');
            if (category === 'all') {
                filteredProducts = [...allProducts];
            } else {
                filteredProducts = allProducts.filter(p => 
                    p.category === category || p.subcategory === category
                );
            }
            
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            if (searchTerm) {
                filteredProducts = filteredProducts.filter(p => {
                    const productName = p.name.toLowerCase();
                    const productCategory = p.category.toLowerCase();
                    
                    const nameWords = productName.split(/\s+/);
                    const matchesName = nameWords.some(word => word === searchTerm || word.startsWith(searchTerm + ' '));
                    const matchesCategory = productCategory === searchTerm;
                    
                    return matchesName || matchesCategory;
                });
            }
            
            displayProducts(filteredProducts);
        });
    });
    
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        const activeCategory = document.querySelector('.filter-btn.active').getAttribute('data-category');
        
        let productsToSearch = activeCategory === 'all' ? allProducts : allProducts.filter(p => p.category === activeCategory);
        
        if (searchTerm) {
            filteredProducts = productsToSearch.filter(p => {
                const productName = p.name.toLowerCase();
                const productCategory = p.category.toLowerCase();
                
                const nameWords = productName.split(/\s+/);
                const matchesName = nameWords.some(word => word === searchTerm || word.startsWith(searchTerm + ' '));
                const matchesCategory = productCategory === searchTerm;
                
                return matchesName || matchesCategory;
            });
        } else {
            filteredProducts = productsToSearch;
        }
        
        displayProducts(filteredProducts);
    });
    
    const newsletterForm = document.getElementById('newsletterForm');
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input').value;
        showNotification('Thank you for subscribing! Check your email for a 10% discount code.', 'success');
        this.reset();
    });
});

function performSearch(searchTerm, productsToSearch = allProducts) {
    searchTerm = searchTerm.toLowerCase().trim();

    if (searchTerm) {
        filteredProducts = productsToSearch.filter(p => {
            const productName = p.name.toLowerCase();
            const productCategory = p.category.toLowerCase();

            const nameWords = productName.split(/\s+/);
            const matchesName = nameWords.some(word => word === searchTerm || word.startsWith(searchTerm));
            const matchesCategory = productCategory === searchTerm;

            return matchesName || matchesCategory;
        });
    } else {
        filteredProducts = [...productsToSearch];
    }

    displayProducts(filteredProducts);
}

function displayProducts(products) {
    const productsGrid = document.getElementById('productsGrid');
    const noResults = document.getElementById('noResults');
    
    if (products.length === 0) {
        productsGrid.innerHTML = '';
        noResults.style.display = 'block';
        return;
    }
    
    noResults.style.display = 'none';
    
    productsGrid.innerHTML = products.map(product => `
        <div class="product-card" data-product-id="${product.id}">
            <div class="product-image">
                <span class="category-badge">${product.category}</span>
                <img src="${product.image}" alt="${product.name}" onerror="this.src='https://via.placeholder.com/400x500/F5F5DC/3E3E3E?text=${encodeURIComponent(product.name)}'">
                <button class="wishlist-btn" onclick="toggleWishlist(this)"><i class="far fa-heart"></i></button>
            </div>
            <div class="product-info">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-price">PKR ${product.price.toLocaleString()}</p>
                <div class="product-actions">
                    <button class="add-to-cart-btn" onclick="addToCart(${product.id})">Add to Cart</button>
                    <a href="product-details.html?id=${product.id}" class="view-details-btn">View Details</a>
                </div>
            </div>
        </div>
    `).join('');
}

function sortProducts(sortType) {
    switch(sortType) {
        case 'price-low':
            filteredProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            filteredProducts.sort((a, b) => b.price - a.price);
            break;
        case 'name-asc':
            filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'name-desc':
            filteredProducts.sort((a, b) => b.name.localeCompare(a.name));
            break;
        default:
            const activeCategory = document.querySelector('.filter-btn.active').getAttribute('data-category');
            filteredProducts = activeCategory === 'all' ? [...allProducts] : allProducts.filter(p => p.category === activeCategory);
    }
    
    displayProducts(filteredProducts);
}

function toggleWishlist(btn) {
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    if (btn.classList.contains('active')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        showNotification('Added to wishlist!', 'success');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        showNotification('Removed from wishlist!', 'success');
    }
}

if (typeof window !== 'undefined') {
    window.allProducts = allProducts;
}
