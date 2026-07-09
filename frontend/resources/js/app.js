import './bootstrap';
import i18next from 'i18next';

const translationResources = {
    en: {
        translation: {},
    },
    fil: {
        translation: {
            'Loading': 'Naglo-load',
            'Home': 'Home',
            'Jobs': 'Mga Trabaho',
            'Find Jobs': 'Maghanap ng Trabaho',
            'FAQ': 'FAQ',
            'About Us': 'Tungkol Sa Amin',
            'Contact Us': 'Makipag-ugnayan',
            'Tutorial': 'Gabay',
            'Register as Applicant': 'Magrehistro bilang Aplikante',
            'Register as Employer': 'Magrehistro bilang Employer',
            'How to Apply Work': 'Paano Mag-apply sa Trabaho',
            'Login': 'Mag-login',
            'Create An Account': 'Gumawa ng Account',
            'Create Account': 'Gumawa ng Account',
            'Register': 'Magrehistro',
            'Accessibility': 'Accessibility',
            'Open accessibility settings': 'Buksan ang accessibility settings',
            'Close accessibility settings': 'Isara ang accessibility settings',
            'Dark Mode': 'Dark Mode',
            'Dyslexia Support': 'Suporta sa Dyslexia',
            'Font Size': 'Laki ng Font',
            'Small': 'Maliit',
            'Normal': 'Normal',
            'Large': 'Malaki',
            'Contrast': 'Contrast',
            'Low': 'Mababa',
            'High': 'Mataas',
            'Saturation': 'Saturation',
            'Translation': 'Pagsasalin',
            'Translation language': 'Wika ng pagsasalin',
            'English': 'English',
            'Filipino': 'Filipino',
            'Bisaya': 'Bisaya',
            'Ilocano': 'Ilocano',
            'Reset Settings': 'I-reset ang Settings',
            'Hireable Proximity': 'Hireable Proximity',
            'Find inclusive Job': 'Humanap ng inklusibong trabaho',
            'Opportunities': 'Mga Oportunidad',
            'Find inclusive Job Opportunities': 'Humanap ng inklusibong oportunidad sa trabaho',
            'Build a brighter future.': 'Bumuo ng mas maliwanag na kinabukasan.',
            'Building a more inclusive future by helping Persons with Disabilities discover rewarding careers, develop their potential.': 'Bumubuo ng mas inklusibong kinabukasan sa pagtulong sa Persons with Disabilities na makahanap ng makabuluhang karera at mapaunlad ang kanilang kakayahan.',
            'Employment Assistant For': 'Employment Assistant Para sa',
            'Persons with a Disability': 'Persons with a Disability',
            'Powered by Decision': 'Pinapagana ng Decision',
            'Support System': 'Support System',
            'City of Dasmarinas': 'Lungsod ng Dasmarinas',
            'Browse Jobs': 'Tingnan ang Trabaho',
            'Start Journey': 'Simulan',
            "Together, we're building opportunities for every Filipino with disabilities.": 'Sama-sama tayong gumagawa ng oportunidad para sa bawat Pilipinong may kapansanan.',
            'PWD Applicants': 'PWD Applicants',
            'Build a profile and apply for inclusive opportunities.': 'Gumawa ng profile at mag-apply sa inklusibong oportunidad.',
            'Available Jobs': 'Mga Bakanteng Trabaho',
            'Browse openings prepared for accessible hiring.': 'Tingnan ang mga bukas na trabahong handa para sa accessible hiring.',
            'Partner Employers': 'Partner Employers',
            'Connect with workplaces that support fair hiring.': 'Kumonekta sa mga trabahong sumusuporta sa patas na hiring.',
            'Featured Job Listings': 'Mga Featured na Trabaho',
            'Explore inclusive job opportunities designed to match the skills, talents, and abilities of Persons with Disabilities.': 'Tuklasin ang inklusibong oportunidad sa trabaho na akma sa skills, talento, at kakayahan ng Persons with Disabilities.',
            'Search jobs': 'Maghanap ng trabaho',
            'Search job Title, Keyword or Company': 'Maghanap ng titulo ng trabaho, keyword, o kumpanya',
            'Category': 'Kategorya',
            'Location': 'Lokasyon',
            'Search Jobs': 'Maghanap ng Trabaho',
            'All Jobs': 'Lahat ng Trabaho',
            'Full time': 'Full time',
            'Part Time': 'Part Time',
            'No Job Posting Yet': 'Wala pang Job Posting',
            'Job listings will appear here once you add your first post.': 'Lalabas dito ang mga job listing kapag nagdagdag na ng unang post.',
            'Frequently Asked Questions': 'Madalas Itanong',
            'Frequently Asked': 'Madalas Itanong',
            'Questions': 'Mga Tanong',
            'Find answers to common questions about the PWD Job Employment.': 'Hanapin ang sagot sa karaniwang tanong tungkol sa PWD Job Employment.',
            'What is the PWD Employment Assistance System?': 'Ano ang PWD Employment Assistance System?',
            'It is a platform that helps Persons with Disabilities find inclusive job opportunities and connect with employers.': 'Ito ay platform na tumutulong sa Persons with Disabilities na makahanap ng inklusibong trabaho at makakonekta sa employers.',
            'Who can register on this platform?': 'Sino ang puwedeng magrehistro sa platform na ito?',
            'PWD applicants and inclusive employers can create an account and use the platform.': 'PWD applicants at inclusive employers ay puwedeng gumawa ng account at gamitin ang platform.',
            'What is the Decision Support System?': 'Ano ang Decision Support System?',
            'It helps organize job matching by considering applicant information, skills, and job requirements.': 'Tumutulong ito sa pag-aayos ng job matching gamit ang impormasyon, skills, at job requirements.',
            'How will I know if my application has been accepted?': 'Paano ko malalaman kung tanggap ang application ko?',
            'Application updates will be shown in your account once the employer reviews your submission.': 'Makikita ang updates sa iyong account kapag nareview na ng employer ang iyong submission.',
            'What should I do if I forget my password?': 'Ano ang gagawin ko kung nakalimutan ko ang password ko?',
            'Use the forgot password option on the login page or contact support for help recovering your account.': 'Gamitin ang forgot password sa login page o makipag-ugnayan sa support para mabawi ang account.',
            'How can I contact support?': 'Paano ako makakontak sa support?',
            'You can use the Help Center or Contact Us page to send your concern to the support team.': 'Puwede mong gamitin ang Help Center o Contact Us page para ipadala ang concern mo sa support team.',
            'Explore': 'Explore',
            'Support': 'Support',
            'Accessibility Policy': 'Accessibility Policy',
            'Terms and Conditions': 'Terms and Conditions',
            'Privacy Policy': 'Privacy Policy',
            'Help Center': 'Help Center',
            'Resources': 'Resources',
            'All right reserved': 'All rights reserved',
            'Designed to support inclusive hiring and accessible career growth.': 'Dinisenyo para suportahan ang inklusibong hiring at accessible career growth.',
            'Find Work Built Around Ability': 'Humanap ng trabahong nakabatay sa kakayahan',
            'A focused space for PWD applicants and inclusive employers to meet, apply, and manage opportunities with confidence.': 'Isang malinaw na espasyo para sa PWD applicants at inclusive employers na magkita, mag-apply, at mamahala ng oportunidad nang may kumpiyansa.',
            'Guided applications for PWD applicants': 'May gabay na application para sa PWD applicants',
            'Inclusive job matching with partner employers': 'Inklusibong job matching kasama ang partner employers',
            'Simple access for applicants and employers': 'Madaling access para sa applicants at employers',
            'Welcome back!': 'Maligayang pagbabalik!',
            'Sign in to continue your inclusive job search.': 'Mag-login para ipagpatuloy ang paghahanap ng inklusibong trabaho.',
            'Email Address': 'Email Address',
            'you@example.com': 'you@example.com',
            'Password': 'Password',
            'Forgot password?': 'Nakalimutan ang password?',
            'Enter your password': 'Ilagay ang password',
            'Remember me for 30 days': 'Tandaan ako sa loob ng 30 araw',
            "Don't have an account?": 'Wala ka pang account?',
            'Create your account': 'Gumawa ng account',
            'Choose your account type, then complete the required setup.': 'Piliin ang uri ng account, pagkatapos kumpletuhin ang kailangan.',
            'Complete each applicant step before account setup.': 'Kumpletuhin muna ang bawat hakbang ng aplikante bago ang account setup.',
            'Create your employer account with your email and password.': 'Gumawa ng employer account gamit ang email at password.',
            'Basic Info': 'Basic Info',
            'Verification': 'Verification',
            'Account Setup': 'Account Setup',
            'Account Type': 'Uri ng Account',
            'PWD Applicant': 'PWD Applicant',
            'Find inclusive work': 'Maghanap ng inklusibong trabaho',
            'Employer': 'Employer',
            'Post inclusive jobs': 'Mag-post ng inklusibong trabaho',
            'First Name': 'Pangalan',
            'First name': 'Pangalan',
            'Last Name': 'Apelyido',
            'Last name': 'Apelyido',
            'Gender': 'Kasarian',
            'Select': 'Pumili',
            'Female': 'Babae',
            'Male': 'Lalaki',
            'Non-binary': 'Non-binary',
            'Prefer not to say': 'Ayaw sabihin',
            'Age': 'Edad',
            'Birthdate': 'Petsa ng Kapanganakan',
            'Disability': 'Kapansanan',
            'Select disability': 'Pumili ng kapansanan',
            'Hearing Disability': 'Kapansanan sa Pandinig',
            'Visual Disability': 'Kapansanan sa Paningin',
            'Physical Disability': 'Pisikal na Kapansanan',
            'Speech Disability': 'Kapansanan sa Pagsasalita',
            'Psychosocial Disability': 'Psychosocial Disability',
            'Intellectual Disability': 'Intellectual Disability',
            'Multiple Disability': 'Multiple Disability',
            'Street Address': 'Street Address',
            'Select Dasmarinas address': 'Pumili ng address sa Dasmarinas',
            'City': 'Lungsod',
            'PWD ID Verification': 'PWD ID Verification',
            'Upload JPG, PNG, or PDF. Maximum file size is 5MB.': 'Mag-upload ng JPG, PNG, o PDF. Maximum file size ay 5MB.',
            'Contact Number': 'Contact Number',
            '09XX XXX XXXX': '09XX XXX XXXX',
            'Create a password': 'Gumawa ng password',
            'Confirm Password': 'Kumpirmahin ang Password',
            'Confirm password': 'Kumpirmahin ang password',
            'I confirm that my information is correct and my PWD ID is valid for verification.': 'Kinukumpirma ko na tama ang impormasyon ko at valid ang PWD ID ko para sa verification.',
            'Back': 'Balik',
            'Next': 'Susunod',
            'Already have an account?': 'May account ka na?',
            'Passwords must match.': 'Dapat magkapareho ang password.',
        },
    },
    ceb: {
        translation: {
            'Loading': 'Nag-load',
            'Home': 'Home',
            'Jobs': 'Mga Trabaho',
            'Find Jobs': 'Pangita Trabaho',
            'FAQ': 'FAQ',
            'About Us': 'Mahitungod Kanamo',
            'Contact Us': 'Kontaka Kami',
            'Tutorial': 'Giya',
            'Register as Applicant': 'Rehistro isip Aplikante',
            'Register as Employer': 'Rehistro isip Employer',
            'How to Apply Work': 'Unsaon Pag-apply sa Trabaho',
            'Login': 'Login',
            'Create An Account': 'Paghimo og Account',
            'Create Account': 'Paghimo og Account',
            'Register': 'Rehistro',
            'Accessibility': 'Accessibility',
            'Open accessibility settings': 'Ablihi ang accessibility settings',
            'Close accessibility settings': 'Siradhi ang accessibility settings',
            'Dark Mode': 'Dark Mode',
            'Dyslexia Support': 'Suporta sa Dyslexia',
            'Font Size': 'Kadako sa Font',
            'Small': 'Gamay',
            'Normal': 'Normal',
            'Large': 'Dako',
            'Contrast': 'Contrast',
            'Low': 'Ubos',
            'High': 'Taas',
            'Saturation': 'Saturation',
            'Translation': 'Hubad',
            'Translation language': 'Pinulongan sa hubad',
            'English': 'English',
            'Filipino': 'Filipino',
            'Bisaya': 'Bisaya',
            'Ilocano': 'Ilocano',
            'Reset Settings': 'I-reset ang Settings',
            'Find inclusive Job': 'Pangita inklusibong trabaho',
            'Opportunities': 'Mga Oportunidad',
            'Find inclusive Job Opportunities': 'Pangita og inklusibong oportunidad sa trabaho',
            'Build a brighter future.': 'Paghimo og mas hayag nga kaugmaon.',
            'Building a more inclusive future by helping Persons with Disabilities discover rewarding careers, develop their potential.': 'Nagtukod og mas inklusibong kaugmaon pinaagi sa pagtabang sa Persons with Disabilities nga makakita og maayong karera ug mapalambo ang ilang potensyal.',
            'Employment Assistant For': 'Employment Assistant Para sa',
            'Persons with a Disability': 'Persons with a Disability',
            'Powered by Decision': 'Gipagana sa Decision',
            'Support System': 'Support System',
            'City of Dasmarinas': 'Dakbayan sa Dasmarinas',
            'Browse Jobs': 'Tan-aw Trabaho',
            'Start Journey': 'Sugdi',
            "Together, we're building opportunities for every Filipino with disabilities.": 'Mag-uban kita sa paghimo og oportunidad para sa matag Pilipino nga adunay disability.',
            'PWD Applicants': 'PWD Applicants',
            'Build a profile and apply for inclusive opportunities.': 'Paghimo og profile ug apply sa inklusibong oportunidad.',
            'Available Jobs': 'Bakanteng Trabaho',
            'Browse openings prepared for accessible hiring.': 'Tan-awa ang openings nga giandam para sa accessible hiring.',
            'Partner Employers': 'Partner Employers',
            'Connect with workplaces that support fair hiring.': 'Konektar sa workplaces nga mosuporta sa patas nga hiring.',
            'Featured Job Listings': 'Featured nga Job Listings',
            'Explore inclusive job opportunities designed to match the skills, talents, and abilities of Persons with Disabilities.': 'Susiha ang inklusibong trabaho nga moangay sa skills, talento, ug abilidad sa Persons with Disabilities.',
            'Search jobs': 'Pangita trabaho',
            'Search job Title, Keyword or Company': 'Pangita titulo sa trabaho, keyword, o kompanya',
            'Category': 'Kategorya',
            'Location': 'Lokasyon',
            'Search Jobs': 'Pangita Trabaho',
            'All Jobs': 'Tanang Trabaho',
            'Full time': 'Full time',
            'Part Time': 'Part Time',
            'No Job Posting Yet': 'Wala pay Job Posting',
            'Job listings will appear here once you add your first post.': 'Mugawas diri ang job listings kung makadugang naka sa unang post.',
            'Frequently Asked Questions': 'Kanunay nga Pangutana',
            'Frequently Asked': 'Kanunay nga',
            'Questions': 'Mga Pangutana',
            'Find answers to common questions about the PWD Job Employment.': 'Pangitaa ang tubag sa kasagarang pangutana bahin sa PWD Job Employment.',
            'What is the PWD Employment Assistance System?': 'Unsa ang PWD Employment Assistance System?',
            'It is a platform that helps Persons with Disabilities find inclusive job opportunities and connect with employers.': 'Kini usa ka platform nga motabang sa Persons with Disabilities makakita og inklusibong trabaho ug makakonektar sa employers.',
            'Who can register on this platform?': 'Kinsa ang makarehistro ani nga platform?',
            'PWD applicants and inclusive employers can create an account and use the platform.': 'PWD applicants ug inclusive employers makapaghimo og account ug magamit ang platform.',
            'What is the Decision Support System?': 'Unsa ang Decision Support System?',
            'It helps organize job matching by considering applicant information, skills, and job requirements.': 'Motabang kini sa pag-organize sa job matching gamit ang impormasyon, skills, ug job requirements.',
            'How will I know if my application has been accepted?': 'Unsaon nako pagkahibalo kung nadawat akong application?',
            'Application updates will be shown in your account once the employer reviews your submission.': 'Makita ang updates sa imong account kung mareview na sa employer ang imong submission.',
            'What should I do if I forget my password?': 'Unsa akong buhaton kung nakalimot ko sa password?',
            'Use the forgot password option on the login page or contact support for help recovering your account.': 'Gamita ang forgot password sa login page o kontak support para matabangan ka mabawi ang account.',
            'How can I contact support?': 'Unsaon nako pagkontak sa support?',
            'You can use the Help Center or Contact Us page to send your concern to the support team.': 'Pwede nimo gamiton ang Help Center o Contact Us page para ipadala ang imong concern sa support team.',
            'Explore': 'Explore',
            'Support': 'Support',
            'Accessibility Policy': 'Accessibility Policy',
            'Terms and Conditions': 'Terms and Conditions',
            'Privacy Policy': 'Privacy Policy',
            'Help Center': 'Help Center',
            'Resources': 'Resources',
            'Designed to support inclusive hiring and accessible career growth.': 'Gidisenyo para mosuporta sa inklusibong hiring ug accessible career growth.',
            'Find Work Built Around Ability': 'Pangita trabaho nga base sa abilidad',
            'A focused space for PWD applicants and inclusive employers to meet, apply, and manage opportunities with confidence.': 'Espasyo para sa PWD applicants ug inclusive employers nga magkita, mo-apply, ug modumala sa oportunidad nga kampante.',
            'Guided applications for PWD applicants': 'Giyahan nga applications para sa PWD applicants',
            'Inclusive job matching with partner employers': 'Inklusibong job matching uban ang partner employers',
            'Simple access for applicants and employers': 'Sayon nga access para sa applicants ug employers',
            'Welcome back!': 'Maayong pagbalik!',
            'Sign in to continue your inclusive job search.': 'Login para ipadayon ang imong pagpangita og inklusibong trabaho.',
            'Email Address': 'Email Address',
            'Password': 'Password',
            'Forgot password?': 'Nakalimot sa password?',
            'Enter your password': 'Isulod ang password',
            'Remember me for 30 days': 'Hinumdumi ko sulod sa 30 ka adlaw',
            "Don't have an account?": 'Wala kay account?',
            'Create your account': 'Paghimo sa imong account',
            'Choose your account type, then complete the required setup.': 'Pilia ang account type, unya kompletuhon ang gikinahanglan.',
            'Complete each applicant step before account setup.': 'Kompletuhon usa ang matag applicant step sa dili pa ang account setup.',
            'Create your employer account with your email and password.': 'Paghimo og employer account gamit ang email ug password.',
            'Basic Info': 'Basic Info',
            'Verification': 'Verification',
            'Account Setup': 'Account Setup',
            'Account Type': 'Account Type',
            'PWD Applicant': 'PWD Applicant',
            'Find inclusive work': 'Pangita inklusibong trabaho',
            'Employer': 'Employer',
            'Post inclusive jobs': 'Post og inklusibong trabaho',
            'First Name': 'Pangalan',
            'First name': 'Pangalan',
            'Last Name': 'Apelyido',
            'Last name': 'Apelyido',
            'Gender': 'Gender',
            'Select': 'Pili',
            'Female': 'Babaye',
            'Male': 'Lalaki',
            'Non-binary': 'Non-binary',
            'Prefer not to say': 'Dili gusto mosulti',
            'Age': 'Edad',
            'Birthdate': 'Petsa sa Pagkatawo',
            'Disability': 'Disability',
            'Select disability': 'Pili og disability',
            'Hearing Disability': 'Disability sa Pandungog',
            'Visual Disability': 'Disability sa Panan-aw',
            'Physical Disability': 'Pisikal nga Disability',
            'Speech Disability': 'Disability sa Pagsulti',
            'Psychosocial Disability': 'Psychosocial Disability',
            'Intellectual Disability': 'Intellectual Disability',
            'Multiple Disability': 'Multiple Disability',
            'Street Address': 'Street Address',
            'Select Dasmarinas address': 'Pili og address sa Dasmarinas',
            'City': 'Dakbayan',
            'PWD ID Verification': 'PWD ID Verification',
            'Upload JPG, PNG, or PDF. Maximum file size is 5MB.': 'Upload og JPG, PNG, o PDF. Maximum file size kay 5MB.',
            'Contact Number': 'Contact Number',
            'Create a password': 'Paghimo og password',
            'Confirm Password': 'Kumpirmaha ang Password',
            'Confirm password': 'Kumpirmaha ang password',
            'I confirm that my information is correct and my PWD ID is valid for verification.': 'Gikumpirma nako nga sakto akong impormasyon ug valid akong PWD ID para sa verification.',
            'Back': 'Balik',
            'Next': 'Sunod',
            'Already have an account?': 'Naa na kay account?',
            'Passwords must match.': 'Kinahanglan pareho ang password.',
        },
    },
    ilo: {
        translation: {
            'Loading': 'Agloload',
            'Home': 'Home',
            'Jobs': 'Dagiti Trabaho',
            'Find Jobs': 'Agsapul ti Trabaho',
            'FAQ': 'FAQ',
            'About Us': 'Maipapan Kadakami',
            'Contact Us': 'Kontaken Dakami',
            'Tutorial': 'Gabay',
            'Register as Applicant': 'Agrehistro kas Applicant',
            'Register as Employer': 'Agrehistro kas Employer',
            'How to Apply Work': 'Kasano ti Ag-apply iti Trabaho',
            'Login': 'Login',
            'Create An Account': 'Mangaramid ti Account',
            'Create Account': 'Mangaramid ti Account',
            'Register': 'Agrehistro',
            'Accessibility': 'Accessibility',
            'Open accessibility settings': 'Lukatan ti accessibility settings',
            'Close accessibility settings': 'Iserra ti accessibility settings',
            'Dark Mode': 'Dark Mode',
            'Dyslexia Support': 'Tulong para Dyslexia',
            'Font Size': 'Kadakkel ti Font',
            'Small': 'Bassit',
            'Normal': 'Normal',
            'Large': 'Dakkel',
            'Contrast': 'Contrast',
            'Low': 'Nababa',
            'High': 'Nangato',
            'Saturation': 'Saturation',
            'Translation': 'Pannakaipatarus',
            'Translation language': 'Pagsasao ti pannakaipatarus',
            'English': 'English',
            'Filipino': 'Filipino',
            'Bisaya': 'Bisaya',
            'Ilocano': 'Ilocano',
            'Reset Settings': 'I-reset ti Settings',
            'Find inclusive Job': 'Agsapul ti inklusibo a trabaho',
            'Opportunities': 'Dagiti Oportunidad',
            'Find inclusive Job Opportunities': 'Agsapul ti inklusibo nga oportunidad iti trabaho',
            'Build a brighter future.': 'Mangbangon ti nalawlawag a masakbayan.',
            'Building a more inclusive future by helping Persons with Disabilities discover rewarding careers, develop their potential.': 'Mangbangbangon ti mas inklusibo a masakbayan babaen ti panangtulong kadagiti Persons with Disabilities a makasarak ti nasayaat a karera ken mapasayaat ti kabaelanda.',
            'Employment Assistant For': 'Employment Assistant Para Kadagiti',
            'Persons with a Disability': 'Persons with a Disability',
            'Powered by Decision': 'Pinapabileg ti Decision',
            'Support System': 'Support System',
            'City of Dasmarinas': 'Siudad ti Dasmarinas',
            'Browse Jobs': 'Kitaen Dagiti Trabaho',
            'Start Journey': 'Rugian',
            "Together, we're building opportunities for every Filipino with disabilities.": 'Agkakadua tayo a mangaramid ti oportunidad para iti tunggal Pilipino nga addaan disability.',
            'PWD Applicants': 'PWD Applicants',
            'Build a profile and apply for inclusive opportunities.': 'Mangaramid ti profile ken ag-apply iti inklusibo nga oportunidad.',
            'Available Jobs': 'Dagiti Available a Trabaho',
            'Browse openings prepared for accessible hiring.': 'Kitaen dagiti openings a naisagana para accessible hiring.',
            'Partner Employers': 'Partner Employers',
            'Connect with workplaces that support fair hiring.': 'Makikonekta kadagiti pagtrabahuan a mangsuporta iti patas a hiring.',
            'Featured Job Listings': 'Featured Job Listings',
            'Explore inclusive job opportunities designed to match the skills, talents, and abilities of Persons with Disabilities.': 'Sukisoken dagiti inklusibo nga trabaho a maiyataday kadagiti skills, talento, ken kabaelan dagiti Persons with Disabilities.',
            'Search jobs': 'Agsapul ti trabaho',
            'Search job Title, Keyword or Company': 'Agsapul ti titulo ti trabaho, keyword, wenno kumpanya',
            'Category': 'Kategoria',
            'Location': 'Lokasyon',
            'Search Jobs': 'Agsapul ti Trabaho',
            'All Jobs': 'Amin a Trabaho',
            'Full time': 'Full time',
            'Part Time': 'Part Time',
            'No Job Posting Yet': 'Awan pay Job Posting',
            'Job listings will appear here once you add your first post.': 'Agparangto ditoy dagiti job listing no adda nan ti immuna a post.',
            'Frequently Asked Questions': 'Masansan a Saludsod',
            'Frequently Asked': 'Masansan a',
            'Questions': 'Dagiti Saludsod',
            'Find answers to common questions about the PWD Job Employment.': 'Sapulen dagiti sungbat kadagiti kadawyan a saludsod maipapan iti PWD Job Employment.',
            'What is the PWD Employment Assistance System?': 'Ania ti PWD Employment Assistance System?',
            'It is a platform that helps Persons with Disabilities find inclusive job opportunities and connect with employers.': 'Maysa daytoy a platform a tumulong kadagiti Persons with Disabilities nga agsapul ti inklusibo a trabaho ken makikonekta kadagiti employers.',
            'Who can register on this platform?': 'Siasino ti makapagrehistro iti daytoy a platform?',
            'PWD applicants and inclusive employers can create an account and use the platform.': 'Dagiti PWD applicants ken inclusive employers ket mabalin a mangaramid ti account ken usarenda ti platform.',
            'What is the Decision Support System?': 'Ania ti Decision Support System?',
            'It helps organize job matching by considering applicant information, skills, and job requirements.': 'Tumutulong daytoy a mangurnos ti job matching babaen ti applicant information, skills, ken job requirements.',
            'How will I know if my application has been accepted?': 'Kasano ko ammo no naawat ti application ko?',
            'Application updates will be shown in your account once the employer reviews your submission.': 'Makita dagiti updates iti account mo no nareview nan ti employer ti submission mo.',
            'What should I do if I forget my password?': 'Ania ti aramidek no nalipatak ti password?',
            'Use the forgot password option on the login page or contact support for help recovering your account.': 'Usaren ti forgot password iti login page wenno kontaken ti support tapno matulongan ka a makasubli iti account.',
            'How can I contact support?': 'Kasano ko makontak ti support?',
            'You can use the Help Center or Contact Us page to send your concern to the support team.': 'Mabalin mo nga usaren ti Help Center wenno Contact Us page tapno maipatulod ti concern mo iti support team.',
            'Explore': 'Explore',
            'Support': 'Support',
            'Accessibility Policy': 'Accessibility Policy',
            'Terms and Conditions': 'Terms and Conditions',
            'Privacy Policy': 'Privacy Policy',
            'Help Center': 'Help Center',
            'Resources': 'Resources',
            'Designed to support inclusive hiring and accessible career growth.': 'Nadisenyo tapno suportaran ti inklusibo a hiring ken accessible career growth.',
            'Find Work Built Around Ability': 'Agsapul ti trabaho a naibatay iti kabaelan',
            'A focused space for PWD applicants and inclusive employers to meet, apply, and manage opportunities with confidence.': 'Maysa a lugar para kadagiti PWD applicants ken inclusive employers a magkita, ag-apply, ken mangmanage ti oportunidad a sitatalged.',
            'Guided applications for PWD applicants': 'Nagiyahan nga applications para kadagiti PWD applicants',
            'Inclusive job matching with partner employers': 'Inklusibo a job matching kadagiti partner employers',
            'Simple access for applicants and employers': 'Nalaka nga access para kadagiti applicants ken employers',
            'Welcome back!': 'Naragsak a panagsubli!',
            'Sign in to continue your inclusive job search.': 'Login tapno ituloy ti panagsapul mo ti inklusibo a trabaho.',
            'Email Address': 'Email Address',
            'Password': 'Password',
            'Forgot password?': 'Nalipatan ti password?',
            'Enter your password': 'Iserrek ti password',
            'Remember me for 30 days': 'Laglagipen nak iti 30 nga aldaw',
            "Don't have an account?": 'Awan pay account mo?',
            'Create your account': 'Mangaramid ti account',
            'Choose your account type, then complete the required setup.': 'Pilien ti account type, kalpasanna kumpletuen ti masapul.',
            'Complete each applicant step before account setup.': 'Kumpletuen pay ti tunggal applicant step sakbay ti account setup.',
            'Create your employer account with your email and password.': 'Mangaramid ti employer account babaen ti email ken password.',
            'Basic Info': 'Basic Info',
            'Verification': 'Verification',
            'Account Setup': 'Account Setup',
            'Account Type': 'Account Type',
            'PWD Applicant': 'PWD Applicant',
            'Find inclusive work': 'Agsapul ti inklusibo a trabaho',
            'Employer': 'Employer',
            'Post inclusive jobs': 'Agpost ti inklusibo a trabaho',
            'First Name': 'Nagan',
            'First name': 'Nagan',
            'Last Name': 'Apelyido',
            'Last name': 'Apelyido',
            'Gender': 'Gender',
            'Select': 'Pilien',
            'Female': 'Babae',
            'Male': 'Lalaki',
            'Non-binary': 'Non-binary',
            'Prefer not to say': 'Kayat nga saan a ibaga',
            'Age': 'Edad',
            'Birthdate': 'Petsa ti Pannakayanak',
            'Disability': 'Disability',
            'Select disability': 'Pilien ti disability',
            'Hearing Disability': 'Disability iti Panagdengngeg',
            'Visual Disability': 'Disability iti Panagkita',
            'Physical Disability': 'Pisikal a Disability',
            'Speech Disability': 'Disability iti Panagsao',
            'Psychosocial Disability': 'Psychosocial Disability',
            'Intellectual Disability': 'Intellectual Disability',
            'Multiple Disability': 'Multiple Disability',
            'Street Address': 'Street Address',
            'Select Dasmarinas address': 'Pilien ti address iti Dasmarinas',
            'City': 'Siudad',
            'PWD ID Verification': 'PWD ID Verification',
            'Upload JPG, PNG, or PDF. Maximum file size is 5MB.': 'Ag-upload ti JPG, PNG, wenno PDF. Maximum file size ket 5MB.',
            'Contact Number': 'Contact Number',
            'Create a password': 'Mangaramid ti password',
            'Confirm Password': 'Kumpirmaen ti Password',
            'Confirm password': 'Kumpirmaen ti password',
            'I confirm that my information is correct and my PWD ID is valid for verification.': 'Kukumpirmaek a husto ti impormasyok ken valid ti PWD ID ko para verification.',
            'Back': 'Subli',
            'Next': 'Sumaruno',
            'Already have an account?': 'Adda account mo?',
            'Passwords must match.': 'Masapul agpapada dagiti password.',
        },
    },
};

i18next.init({
    resources: translationResources,
    lng: 'en',
    fallbackLng: 'en',
    initImmediate: false,
    interpolation: {
        escapeValue: false,
    },
});

const textNodeOriginals = new WeakMap();
let currentInterfaceLanguage = 'en';

const languageHtmlMap = {
    en: 'en',
    fil: 'fil',
    ceb: 'ceb',
    ilo: 'ilo',
};

const translatePlainText = (value) => {
    const normalized = value.replace(/\s+/g, ' ').trim();

    if (!normalized) {
        return value;
    }

    return i18next.t(normalized, { defaultValue: normalized });
};

const applyTranslations = (language = currentInterfaceLanguage) => {
    currentInterfaceLanguage = Object.hasOwn(languageHtmlMap, language) ? language : 'en';
    i18next.changeLanguage(currentInterfaceLanguage);
    document.documentElement.lang = languageHtmlMap[currentInterfaceLanguage];

    if (!document.body) {
        return;
    }

    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
        acceptNode(node) {
            if (!node.nodeValue.trim()) {
                return NodeFilter.FILTER_REJECT;
            }

            if (!node.parentElement || node.parentElement.closest('script, style, noscript, svg, [data-no-translate]')) {
                return NodeFilter.FILTER_REJECT;
            }

            return NodeFilter.FILTER_ACCEPT;
        },
    });

    const textNodes = [];
    let currentNode = walker.nextNode();

    while (currentNode) {
        textNodes.push(currentNode);
        currentNode = walker.nextNode();
    }

    textNodes.forEach((node) => {
        if (!textNodeOriginals.has(node)) {
            textNodeOriginals.set(node, node.nodeValue);
        }

        const originalValue = textNodeOriginals.get(node);
        const leadingSpace = originalValue.match(/^\s*/)?.[0] ?? '';
        const trailingSpace = originalValue.match(/\s*$/)?.[0] ?? '';

        node.nodeValue = `${leadingSpace}${translatePlainText(originalValue)}${trailingSpace}`;
    });

    document.querySelectorAll('[placeholder], [aria-label], [title], img[alt]').forEach((element) => {
        ['placeholder', 'aria-label', 'title', 'alt'].forEach((attribute) => {
            const value = element.getAttribute(attribute);

            if (!value?.trim()) {
                return;
            }

            const originalAttribute = `data-translation-original-${attribute}`;

            if (!element.hasAttribute(originalAttribute)) {
                element.setAttribute(originalAttribute, value);
            }

            element.setAttribute(attribute, translatePlainText(element.getAttribute(originalAttribute)));
        });
    });
};

const pageLoader = document.getElementById('page-loader');
const authPaths = new Set(['/login', '/register', '/register/verification']);
let navigationTimer;

const normalizePath = (pathname) => {
    const normalizedPath = pathname.replace(/\/+$/, '');

    return normalizedPath || '/';
};

const isAuthPath = (pathname) => authPaths.has(normalizePath(pathname));

const markPageReady = () => {
    window.requestAnimationFrame(() => {
        document.body.classList.remove('page-entering', 'is-navigating');
        document.body.classList.add('page-is-ready');
        pageLoader?.classList.remove('is-visible');
        pageLoader?.setAttribute('aria-hidden', 'true');
    });
};

const showPageLoader = () => {
    window.clearTimeout(navigationTimer);
    document.body.classList.remove('page-entering', 'page-is-ready');
    document.body.classList.add('is-navigating');
    window.requestAnimationFrame(() => {
        pageLoader?.classList.add('is-visible');
        pageLoader?.setAttribute('aria-hidden', 'false');
    });
};

window.addEventListener('pageshow', markPageReady);

if (pageLoader) {
    document.addEventListener('submit', (event) => {
        if (
            !event.defaultPrevented &&
            event.target instanceof HTMLFormElement &&
            event.target.method.toLowerCase() !== 'get' &&
            !isAuthPath(window.location.pathname)
        ) {
            showPageLoader();
        }
    });

    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || ! (event.target instanceof Element)) {
            return;
        }

        const link = event.target.closest('a[href]');

        if (
            !link ||
            link.target ||
            link.hasAttribute('download') ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        const rawHref = link.getAttribute('href')?.trim() ?? '';

        if (!rawHref || rawHref === '#') {
            return;
        }

        const url = new URL(link.href, window.location.href);
        const isSameOrigin = url.origin === window.location.origin;
        const isSamePageAnchor =
            url.pathname === window.location.pathname &&
            url.search === window.location.search &&
            (rawHref.startsWith('#') || Boolean(url.hash));

        if (
            isSameOrigin &&
            !isSamePageAnchor &&
            !isAuthPath(window.location.pathname) &&
            !isAuthPath(url.pathname)
        ) {
            event.preventDefault();
            showPageLoader();
            navigationTimer = window.setTimeout(() => {
                window.location.assign(url.href);
            }, 430);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', markPageReady, { once: true });
} else {
    markPageReady();
}

if (isAuthPath(window.location.pathname)) {
    document.addEventListener('click', (event) => {
        if (event.defaultPrevented || ! (event.target instanceof Element)) {
            return;
        }

        const link = event.target.closest('a[href]');

        if (
            !link ||
            link.target ||
            link.hasAttribute('download') ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        const url = new URL(link.href, window.location.href);
        const targetPath = normalizePath(url.pathname);

        if (
            url.origin !== window.location.origin ||
            !isAuthPath(targetPath) ||
            targetPath === normalizePath(window.location.pathname)
        ) {
            return;
        }

        event.preventDefault();
        document.body.classList.add('auth-route-leaving');

        window.setTimeout(() => {
            window.location.assign(url.href);
        }, 170);
    });
}

const accessibilityWidget = document.querySelector('[data-accessibility]');

if (accessibilityWidget) {
    const storageKey = 'pwd-accessibility-settings';
    const toggleButton = accessibilityWidget.querySelector('[data-accessibility-toggle]');
    const closeButton = accessibilityWidget.querySelector('[data-accessibility-close]');
    const panel = accessibilityWidget.querySelector('[data-accessibility-panel]');
    const darkInput = accessibilityWidget.querySelector('[data-accessibility-dark]');
    const dyslexiaInput = accessibilityWidget.querySelector('[data-accessibility-dyslexia]');
    const contrastInput = accessibilityWidget.querySelector('[data-accessibility-contrast]');
    const choiceButtons = accessibilityWidget.querySelectorAll('[data-accessibility-choice]');
    const resetButton = accessibilityWidget.querySelector('[data-accessibility-reset]');
    const voiceTextButton = accessibilityWidget.querySelector('[data-voice-text]');
    const voiceTypeButton = accessibilityWidget.querySelector('[data-voice-type]');
    const voiceStatus = accessibilityWidget.querySelector('[data-voice-status]');
    const voiceOutput = accessibilityWidget.querySelector('[data-voice-output]');
    const SpeechRecognitionConstructor = window.SpeechRecognition || window.webkitSpeechRecognition;
    const supportsSpeechRecognition = typeof SpeechRecognitionConstructor === 'function';

    const optionValues = {
        font: {
            small: 0.95,
            normal: 1,
            large: 1.15,
        },
        saturation: {
            low: 0.7,
            normal: 1,
            high: 1.3,
        },
        language: {
            en: 'en',
            fil: 'fil',
            ceb: 'ceb',
            ilo: 'ilo',
        },
    };

    const defaultSettings = {
        dark: false,
        dyslexia: false,
        font: 'normal',
        contrast: false,
        saturation: 'normal',
        language: 'en',
    };

    const normalizeChoice = (key, value) => {
        if (Object.hasOwn(optionValues[key], value)) {
            return value;
        }

        if (typeof value === 'number') {
            if (value < 100) {
                return 'low' in optionValues[key] ? 'low' : 'small';
            }

            if (value > 100) {
                return 'high' in optionValues[key] ? 'high' : 'large';
            }
        }

        return key === 'language' ? 'en' : 'normal';
    };

    const normalizeToggle = (value) => value === true || value === 'true' || value === 'high';

    const readSettings = () => {
        try {
            const savedSettings = { ...defaultSettings, ...JSON.parse(window.localStorage.getItem(storageKey)) };

            return {
                ...savedSettings,
                font: normalizeChoice('font', savedSettings.font),
                contrast: normalizeToggle(savedSettings.contrast),
                saturation: normalizeChoice('saturation', savedSettings.saturation),
                language: normalizeChoice('language', savedSettings.language),
            };
        } catch {
            return { ...defaultSettings };
        }
    };

    let accessibilitySettings = readSettings();
    let speechRecognition = null;
    let isVoiceListening = false;
    let voiceMode = 'text';
    let voiceBaseTranscript = '';
    let typedTranscript = '';
    let lastVoiceTarget = null;

    const saveSettings = () => {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(accessibilitySettings));
        } catch {
            // Storage can fail in private browsing; controls should still work for this page view.
        }
    };

    const syncSwitchControl = (input, isChecked) => {
        input.checked = isChecked;
        input.setAttribute('aria-checked', String(isChecked));

        const state = input.closest('.accessibility-switch')?.querySelector('[data-accessibility-switch-state]');

        if (state) {
            const stateLabel = isChecked ? 'On' : 'Off';
            state.textContent = i18next.t(stateLabel, { defaultValue: stateLabel });
        }
    };

    const syncAccessibilityControls = () => {
        syncSwitchControl(darkInput, accessibilitySettings.dark);
        syncSwitchControl(dyslexiaInput, accessibilitySettings.dyslexia);
        syncSwitchControl(contrastInput, accessibilitySettings.contrast);

        choiceButtons.forEach((button) => {
            const key = button.dataset.accessibilityChoice;
            const isActive = accessibilitySettings[key] === button.dataset.value;

            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });
    };

    const applyAccessibilitySettings = () => {
        document.documentElement.style.setProperty('--accessibility-font-scale', String(optionValues.font[accessibilitySettings.font]));
        document.documentElement.style.setProperty('--accessibility-saturation', String(optionValues.saturation[accessibilitySettings.saturation]));
        document.body.classList.toggle('accessibility-dark', accessibilitySettings.dark);
        document.body.classList.toggle('accessibility-dyslexia', accessibilitySettings.dyslexia);
        document.body.classList.toggle('accessibility-invert', accessibilitySettings.contrast);
        applyTranslations(accessibilitySettings.language);
        syncAccessibilityControls();
        saveSettings();
    };

    const setVoiceStatus = (message) => {
        if (voiceStatus) {
            voiceStatus.textContent = message;
        }
    };

    const isVoiceTypingTarget = (element) => {
        if (!(element instanceof HTMLElement)) {
            return false;
        }

        if (element.isContentEditable) {
            return true;
        }

        if (element instanceof HTMLTextAreaElement) {
            return !element.disabled && !element.readOnly;
        }

        if (!(element instanceof HTMLInputElement)) {
            return false;
        }

        const blockedTypes = new Set([
            'button',
            'checkbox',
            'color',
            'date',
            'file',
            'hidden',
            'image',
            'month',
            'number',
            'password',
            'radio',
            'range',
            'reset',
            'submit',
            'time',
            'week',
        ]);

        return !blockedTypes.has(element.type) && !element.disabled && !element.readOnly;
    };

    const insertVoiceText = (target, text) => {
        if (!target || !text.trim()) {
            return;
        }

        const textToInsert = text.trim();

        if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement) {
            const start = target.selectionStart ?? target.value.length;
            const end = target.selectionEnd ?? target.value.length;
            const prefix = target.value.slice(0, start);
            const suffix = target.value.slice(end);
            const spacer = prefix && !/\s$/.test(prefix) ? ' ' : '';
            const trailingSpacer = suffix && !/^\s/.test(suffix) ? ' ' : '';

            target.value = `${prefix}${spacer}${textToInsert}${trailingSpacer}${suffix}`;
            const cursorPosition = (prefix + spacer + textToInsert).length;
            target.setSelectionRange(cursorPosition, cursorPosition);
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
            target.focus({ preventScroll: true });
            return;
        }

        if (target.isContentEditable) {
            target.focus({ preventScroll: true });
            document.execCommand('insertText', false, textToInsert);
            target.dispatchEvent(new Event('input', { bubbles: true }));
        }
    };

    const syncVoiceButtons = () => {
        voiceTextButton?.classList.toggle('is-listening', isVoiceListening && voiceMode === 'text');
        voiceTypeButton?.classList.toggle('is-listening', isVoiceListening && voiceMode === 'type');
        voiceTextButton?.setAttribute('aria-pressed', String(isVoiceListening && voiceMode === 'text'));
        voiceTypeButton?.setAttribute('aria-pressed', String(isVoiceListening && voiceMode === 'type'));
    };

    const getVoiceLanguage = () => {
        const languageMap = {
            en: 'en-US',
            fil: 'fil-PH',
            ceb: 'en-PH',
            ilo: 'en-PH',
        };

        return languageMap[currentInterfaceLanguage] ?? 'en-US';
    };

    const getSpeechRecognition = () => {
        if (!supportsSpeechRecognition) {
            return null;
        }

        if (speechRecognition) {
            return speechRecognition;
        }

        speechRecognition = new SpeechRecognitionConstructor();
        speechRecognition.continuous = false;
        speechRecognition.interimResults = true;

        speechRecognition.onstart = () => {
            isVoiceListening = true;
            setVoiceStatus('Listening');
            syncVoiceButtons();
        };

        speechRecognition.onend = () => {
            isVoiceListening = false;
            setVoiceStatus('Ready');
            syncVoiceButtons();
        };

        speechRecognition.onerror = () => {
            isVoiceListening = false;
            setVoiceStatus('Mic unavailable');
            syncVoiceButtons();
        };

        speechRecognition.onresult = (event) => {
            let finalTranscript = '';
            let interimTranscript = '';

            for (let index = 0; index < event.results.length; index += 1) {
                const transcript = event.results[index][0]?.transcript.trim() ?? '';

                if (!transcript) {
                    continue;
                }

                if (event.results[index].isFinal) {
                    finalTranscript = `${finalTranscript} ${transcript}`.trim();
                } else {
                    interimTranscript = `${interimTranscript} ${transcript}`.trim();
                }
            }

            if (voiceMode === 'type') {
                const newText = finalTranscript.slice(typedTranscript.length).trim();

                if (newText) {
                    insertVoiceText(lastVoiceTarget, newText);
                    typedTranscript = finalTranscript;
                }

                if (voiceOutput) {
                    voiceOutput.value = [typedTranscript, interimTranscript].filter(Boolean).join(' ');
                }

                return;
            }

            if (voiceOutput) {
                voiceOutput.value = [voiceBaseTranscript, finalTranscript, interimTranscript]
                    .filter(Boolean)
                    .join(' ')
                    .trim();
            }
        };

        return speechRecognition;
    };

    const startVoiceInput = (mode) => {
        const recognition = getSpeechRecognition();

        if (!recognition) {
            setVoiceStatus('Not supported');
            return;
        }

        if (isVoiceListening) {
            recognition.stop();

            if (voiceMode === mode) {
                return;
            }
        }

        voiceMode = mode;
        voiceBaseTranscript = voiceOutput?.value.trim() ?? '';
        typedTranscript = '';

        if (mode === 'type') {
            const activeElement = document.activeElement;

            if (isVoiceTypingTarget(activeElement)) {
                lastVoiceTarget = activeElement;
            }

            if (!lastVoiceTarget) {
                setVoiceStatus('Focus field first');
                return;
            }
        }

        recognition.lang = getVoiceLanguage();

        try {
            recognition.start();
        } catch {
            setVoiceStatus('Try again');
        }
    };

    const setAccessibilityPanel = (isOpen) => {
        toggleButton.setAttribute('aria-expanded', String(isOpen));

        if (isOpen) {
            panel.hidden = false;
            window.requestAnimationFrame(() => panel.classList.add('is-open'));
            return;
        }

        panel.classList.remove('is-open');
        window.setTimeout(() => {
            if (toggleButton.getAttribute('aria-expanded') !== 'true') {
                panel.hidden = true;
            }
        }, 180);
    };

    toggleButton.addEventListener('click', () => {
        setAccessibilityPanel(toggleButton.getAttribute('aria-expanded') !== 'true');
    });

    closeButton.addEventListener('click', () => setAccessibilityPanel(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setAccessibilityPanel(false);
        }
    });

    document.addEventListener('click', (event) => {
        if (!accessibilityWidget.contains(event.target)) {
            setAccessibilityPanel(false);
        }
    });

    document.addEventListener('focusin', (event) => {
        if (isVoiceTypingTarget(event.target)) {
            lastVoiceTarget = event.target;
        }
    });

    if (!supportsSpeechRecognition) {
        voiceTextButton?.setAttribute('disabled', 'disabled');
        voiceTypeButton?.setAttribute('disabled', 'disabled');
        setVoiceStatus('Not supported');
    }

    voiceTextButton?.addEventListener('click', () => startVoiceInput('text'));
    voiceTypeButton?.addEventListener('click', () => startVoiceInput('type'));

    darkInput.addEventListener('change', () => {
        accessibilitySettings.dark = darkInput.checked;
        applyAccessibilitySettings();
    });

    dyslexiaInput.addEventListener('change', () => {
        accessibilitySettings.dyslexia = dyslexiaInput.checked;
        applyAccessibilitySettings();
    });

    contrastInput.addEventListener('change', () => {
        accessibilitySettings.contrast = contrastInput.checked;
        applyAccessibilitySettings();
    });

    choiceButtons.forEach((button) => {
        button.addEventListener('click', () => {
            accessibilitySettings[button.dataset.accessibilityChoice] = button.dataset.value;
            applyAccessibilitySettings();
        });
    });

    resetButton.addEventListener('click', () => {
        accessibilitySettings = { ...defaultSettings };
        applyAccessibilitySettings();
    });

    applyAccessibilitySettings();
}

const navbar = document.getElementById('site-navbar');

if (navbar) {
    const syncNavbar = () => {
        navbar.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    syncNavbar();
    window.addEventListener('scroll', syncNavbar, { passive: true });
}

const dropdown = document.querySelector('[data-dropdown]');

if (dropdown) {
    const button = dropdown.querySelector('[data-dropdown-button]');
    const menu = dropdown.querySelector('[data-dropdown-menu]');
    const icon = dropdown.querySelector('[data-dropdown-icon]');

    const setOpen = (isOpen) => {
        button.setAttribute('aria-expanded', String(isOpen));
        menu.classList.toggle('invisible', !isOpen);
        menu.classList.toggle('opacity-0', !isOpen);
        menu.classList.toggle('opacity-100', isOpen);
        icon.classList.toggle('rotate-180', isOpen);
    };

    button.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        setOpen(button.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('click', (event) => {
        if (!dropdown.contains(event.target)) {
            setOpen(false);
        }
    });

    dropdown.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });
}

const dashboardNotificationToggle = document.querySelector('[data-dashboard-notification-toggle]');
const dashboardNotificationMenu = document.querySelector('[data-dashboard-notification-menu]');

if (dashboardNotificationToggle && dashboardNotificationMenu) {
    const setDashboardNotificationsOpen = (isOpen) => {
        dashboardNotificationToggle.setAttribute('aria-expanded', String(isOpen));
        dashboardNotificationMenu.hidden = !isOpen;
    };

    dashboardNotificationToggle.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        setDashboardNotificationsOpen(dashboardNotificationToggle.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('click', (event) => {
        if (
            event.target instanceof Element &&
            !dashboardNotificationToggle.contains(event.target) &&
            !dashboardNotificationMenu.contains(event.target)
        ) {
            setDashboardNotificationsOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setDashboardNotificationsOpen(false);
        }
    });
}

const approvedReviewRedirect = document.querySelector('[data-review-approved-redirect]');

if (approvedReviewRedirect) {
    window.setTimeout(() => {
        window.location.assign(approvedReviewRedirect.dataset.reviewApprovedRedirect);
    }, 1800);
}

const emailVerifiedRedirect = document.querySelector('[data-email-verified-redirect]');

if (emailVerifiedRedirect) {
    window.setTimeout(() => {
        window.location.assign(emailVerifiedRedirect.dataset.emailVerifiedRedirect);
    }, 1800);
}

const heroSlides = document.querySelectorAll('[data-hero-slide]');
const heroCopies = document.querySelectorAll('[data-hero-copy]');

if (heroSlides.length > 1) {
    const silverLine = document.querySelector('[data-hero-slide-line]');
    let currentSlide = 0;

    const showSlide = (nextSlide) => {
        heroSlides[currentSlide].classList.remove('is-active');
        heroSlides[nextSlide].classList.add('is-active');

        if (heroCopies.length > 1) {
            const currentCopy = currentSlide % heroCopies.length;
            const nextCopy = nextSlide % heroCopies.length;

            heroCopies[currentCopy]?.classList.remove('is-active');
            heroCopies[currentCopy]?.setAttribute('aria-hidden', 'true');
            heroCopies[nextCopy]?.classList.add('is-active');
            heroCopies[nextCopy]?.setAttribute('aria-hidden', 'false');
        }

        currentSlide = nextSlide;

        if (silverLine) {
            silverLine.classList.remove('is-active');
            void silverLine.offsetWidth;
            silverLine.classList.add('is-active');
        }
    };

    window.setInterval(() => {
        showSlide((currentSlide + 1) % heroSlides.length);
    }, 5500);
}

const revealItems = document.querySelectorAll('[data-scroll-reveal]');

if (revealItems.length) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.18 });

    revealItems.forEach((item) => observer.observe(item));
}

const faqToggles = document.querySelectorAll('[data-faq-toggle]');

if (faqToggles.length) {
    const closeFaqItem = (item, button, panel) => {
        button.setAttribute('aria-expanded', 'false');
        panel.style.maxHeight = `${panel.scrollHeight}px`;

        window.requestAnimationFrame(() => {
            item.classList.remove('is-open');
            panel.style.maxHeight = '0px';
        });

        const hideAfterClose = (event) => {
            if (event.propertyName !== 'max-height' || item.classList.contains('is-open')) {
                return;
            }

            panel.hidden = true;
            panel.removeEventListener('transitionend', hideAfterClose);
        };

        panel.addEventListener('transitionend', hideAfterClose);
    };

    const openFaqItem = (item, button, panel) => {
        panel.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        panel.style.maxHeight = '0px';

        window.requestAnimationFrame(() => {
            item.classList.add('is-open');
            panel.style.maxHeight = `${panel.scrollHeight}px`;
        });
    };

    const syncOpenFaqHeights = () => {
        faqToggles.forEach((button) => {
            const item = button.closest('.faq-item');
            const panel = document.getElementById(button.getAttribute('aria-controls'));

            if (item?.classList.contains('is-open') && panel) {
                panel.style.maxHeight = `${panel.scrollHeight}px`;
            }
        });
    };

    faqToggles.forEach((button) => {
        const item = button.closest('.faq-item');
        const panel = document.getElementById(button.getAttribute('aria-controls'));

        if (!item || !panel) {
            return;
        }

        panel.style.maxHeight = '0px';

        button.addEventListener('click', () => {
            const isOpen = item.classList.contains('is-open');

            if (isOpen) {
                closeFaqItem(item, button, panel);
                return;
            }

            faqToggles.forEach((otherButton) => {
                if (otherButton === button) {
                    return;
                }

                const otherItem = otherButton.closest('.faq-item');
                const otherPanel = document.getElementById(otherButton.getAttribute('aria-controls'));

                if (otherItem?.classList.contains('is-open') && otherPanel) {
                    closeFaqItem(otherItem, otherButton, otherPanel);
                }
            });

            openFaqItem(item, button, panel);
        });
    });

    window.addEventListener('resize', syncOpenFaqHeights);
}

const authTabs = document.querySelectorAll('[data-auth-tab]');
const authPanels = document.querySelectorAll('[data-auth-panel]');
const authSwitches = document.querySelectorAll('[data-auth-switch]');
const authRoleCards = document.querySelectorAll('[data-auth-role]');
const authRoleInput = document.querySelector('[data-auth-role-input]');
const registerForm = document.querySelector('[data-register-form]');
const authNavLinks = document.querySelectorAll('[data-auth-nav-link]');

let setAuthMode = () => {};

if (authTabs.length && authPanels.length) {
    setAuthMode = (mode) => {
        const normalizedMode = mode === 'register' ? 'register' : 'login';

        authTabs.forEach((tab) => {
            const isActive = tab.dataset.authTab === normalizedMode;
            tab.classList.toggle('is-active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });

        authPanels.forEach((panel) => {
            panel.classList.toggle('is-hidden', panel.dataset.authPanel !== normalizedMode);
        });

        const targetPath = normalizedMode === 'register' ? '/register' : '/login';

        if (['/login', '/register'].includes(window.location.pathname) && window.location.pathname !== targetPath) {
            window.history.replaceState({}, '', targetPath);
        }

        applyTranslations(currentInterfaceLanguage);
    };

    authTabs.forEach((tab) => {
        tab.addEventListener('click', () => setAuthMode(tab.dataset.authTab));
    });

    authSwitches.forEach((switchButton) => {
        switchButton.addEventListener('click', () => setAuthMode(switchButton.dataset.authSwitch));
    });
}

if (authNavLinks.length && authPanels.length) {
    authNavLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            const mode = link.dataset.authNavLink;

            if (!mode || !['/login', '/register'].includes(window.location.pathname)) {
                return;
            }

            event.preventDefault();
            setAuthMode(mode);

            const targetPath = new URL(link.href, window.location.origin).pathname;

            if (window.location.pathname !== targetPath) {
                window.history.replaceState({}, '', targetPath);
            }
        });
    });
}

if (registerForm) {
    const registerSteps = registerForm.querySelectorAll('[data-register-step]');
    const registerStepButtons = registerForm.querySelectorAll('[data-register-step-button]');
    const registerPreviousButton = registerForm.querySelector('[data-register-prev]');
    const registerNextButton = registerForm.querySelector('[data-register-next]');
    const registerSubmitButton = registerForm.querySelector('[data-register-submit]');
    const registerStepper = registerForm.querySelector('[data-register-stepper]');
    const registerCopy = registerForm.querySelector('[data-register-copy]');
    const roleSwitchNotice = registerForm.querySelector('[data-role-switch-notice]');
    const birthdateInput = registerForm.querySelector('[data-birthdate]');
    const ageInput = registerForm.querySelector('[data-birth-age]');
    const nameInputs = registerForm.querySelectorAll('[data-name-field]');
    const requiredFields = registerForm.querySelectorAll('[data-step-required]');
    const applicantOnlyControls = registerForm.querySelectorAll('[data-register-step="1"] input, [data-register-step="1"] select, [data-register-step="1"] textarea, [data-register-step="2"] input, [data-register-step="2"] select, [data-register-step="2"] textarea');
    const pwdOnlyElements = registerForm.querySelectorAll('[data-pwd-only]');
    const passwordInput = registerForm.querySelector('#register-password');
    const passwordConfirmationInput = registerForm.querySelector('#register-password-confirmation');
    const pwdIdInput = registerForm.querySelector('#register-pwd-id');
    const pwdIdFileName = registerForm.querySelector('[data-file-name]');
    const defaultPwdIdFileName = pwdIdFileName?.textContent.trim() || 'No file selected';

    let currentRegisterStep = 1;
    let registerAdjustTimer;
    registerForm.noValidate = true;

    const getAccountType = () => (authRoleInput?.value === 'employer' ? 'employer' : 'pwd_applicant');
    const isEmployerAccount = () => getAccountType() === 'employer';
    const getStepsToValidate = () => (isEmployerAccount() ? [3] : [1, 2, 3]);

    const setPasswordConfirmationValidity = () => {
        if (!passwordInput || !passwordConfirmationInput) {
            return;
        }

        const isMismatch =
            passwordConfirmationInput.value.length > 0 &&
            passwordInput.value !== passwordConfirmationInput.value;

        passwordConfirmationInput.setCustomValidity(isMismatch ? i18next.t('Passwords must match.', { defaultValue: 'Passwords must match.' }) : '');
    };

    const syncPwdIdFileName = () => {
        if (!pwdIdInput || !pwdIdFileName) {
            return;
        }

        pwdIdFileName.textContent = pwdIdInput.files?.[0]?.name || defaultPwdIdFileName;
    };

    const hasApplicantDraftData = () => Array.from(applicantOnlyControls).some((field) => {
        if (field.id === 'register-city') {
            return false;
        }

        if (field.type === 'file') {
            return field.files?.length > 0;
        }

        if (field.type === 'checkbox' || field.type === 'radio') {
            return field.checked;
        }

        return field.value.trim() !== '';
    });

    const sanitizeNameInput = (input) => {
        const cleanedValue = input.value
            .replace(/[0-9]/g, '')
            .replace(/\s{2,}/g, ' ');

        if (input.value !== cleanedValue) {
            input.value = cleanedValue;
        }
    };

    const sanitizeAgeInput = () => {
        if (!ageInput) {
            return;
        }

        const cleanedValue = ageInput.value.replace(/\D/g, '').slice(0, 3);

        if (ageInput.value !== cleanedValue) {
            ageInput.value = cleanedValue;
        }
    };

    const syncRequiredFields = () => {
        requiredFields.forEach((field) => {
            const isApplicantOnly =
                field.closest('[data-register-step="1"]') ||
                field.closest('[data-register-step="2"]') ||
                field.closest('[data-pwd-only]');

            field.toggleAttribute('required', !field.disabled && (!isEmployerAccount() || !isApplicantOnly));
        });

        setPasswordConfirmationValidity();
    };

    const getFirstInvalidField = (step) => {
        syncRequiredFields();

        const fields = Array.from(registerForm.querySelectorAll(`[data-register-step="${step}"] [data-step-required]`));

        return fields.find((field) => !field.disabled && !field.checkValidity()) ?? null;
    };

    const reportInvalidField = (field) => {
        window.requestAnimationFrame(() => {
            field.focus({ preventScroll: false });
            field.reportValidity();
        });
    };

    const validateStep = (step) => {
        const invalidField = getFirstInvalidField(step);

        if (!invalidField) {
            return true;
        }

        reportInvalidField(invalidField);
        return false;
    };

    const validateRegistration = () => {
        for (const step of getStepsToValidate()) {
            const invalidField = getFirstInvalidField(step);

            if (invalidField) {
                setRegisterStep(step);
                reportInvalidField(invalidField);
                return false;
            }
        }

        return true;
    };

    const setRegisterStep = (step) => {
        currentRegisterStep = isEmployerAccount() ? 3 : Math.min(3, Math.max(1, Number(step)));

        registerSteps.forEach((panel) => {
            panel.classList.toggle('is-hidden', Number(panel.dataset.registerStep) !== currentRegisterStep);
        });

        registerStepButtons.forEach((button) => {
            const stepNumber = Number(button.dataset.registerStepButton);
            const isActive = stepNumber === currentRegisterStep;

            button.classList.toggle('is-active', isActive);
            button.classList.toggle('is-complete', !isEmployerAccount() && stepNumber < currentRegisterStep);
            button.setAttribute('aria-current', isActive ? 'step' : 'false');
            button.setAttribute('aria-disabled', 'true');
            button.disabled = true;
        });

        registerPreviousButton?.classList.toggle('is-hidden', isEmployerAccount() || currentRegisterStep === 1);
        registerNextButton?.classList.toggle('is-hidden', isEmployerAccount() || currentRegisterStep === 3);
        registerSubmitButton?.classList.toggle('is-hidden', !isEmployerAccount() && currentRegisterStep !== 3);
        syncRequiredFields();
    };

    const syncRegisterMode = () => {
        const isEmployer = isEmployerAccount();

        registerForm.classList.add('is-adjusting');
        window.clearTimeout(registerAdjustTimer);
        registerAdjustTimer = window.setTimeout(() => registerForm.classList.remove('is-adjusting'), 220);

        registerStepper?.classList.toggle('is-hidden', isEmployer);
        if (registerCopy) {
            registerCopy.textContent = isEmployer
                ? 'Create your employer account with your email and password.'
                : 'Complete each applicant step before account setup.';
        }

        applicantOnlyControls.forEach((field) => {
            field.disabled = isEmployer || field.id === 'register-city';
        });

        pwdOnlyElements.forEach((element) => {
            element.classList.toggle('is-hidden', isEmployer);

            element.querySelectorAll('input, select, textarea, button').forEach((field) => {
                field.disabled = isEmployer;
            });
        });

        setRegisterStep(isEmployer ? 3 : 1);
        applyTranslations(currentInterfaceLanguage);
    };

    const setAuthRole = (role) => {
        const normalizedRole = role === 'employer' ? 'employer' : 'pwd_applicant';

        if (
            normalizedRole === 'employer' &&
            getAccountType() === 'pwd_applicant' &&
            hasApplicantDraftData()
        ) {
            roleSwitchNotice?.classList.remove('hidden');
            roleSwitchNotice?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            return;
        }

        roleSwitchNotice?.classList.add('hidden');

        if (authRoleInput) {
            authRoleInput.value = normalizedRole;
        }

        authRoleCards.forEach((roleCard) => {
            const isActive = roleCard.dataset.authRole === normalizedRole;

            roleCard.classList.toggle('is-active', isActive);
            roleCard.setAttribute('aria-pressed', String(isActive));
        });

        syncRegisterMode();
    };

    const syncAgeFromBirthdate = () => {
        if (!birthdateInput?.value || !ageInput) {
            return;
        }

        const birthdate = new Date(`${birthdateInput.value}T00:00:00`);

        if (Number.isNaN(birthdate.getTime())) {
            return;
        }

        const today = new Date();
        let age = today.getFullYear() - birthdate.getFullYear();
        const hasBirthdayPassed =
            today.getMonth() > birthdate.getMonth() ||
            (today.getMonth() === birthdate.getMonth() && today.getDate() >= birthdate.getDate());

        if (!hasBirthdayPassed) {
            age -= 1;
        }

        if (age > 0) {
            ageInput.value = String(age);
        }
    };

    registerStepButtons.forEach((button) => {
        button.disabled = true;
        button.setAttribute('aria-disabled', 'true');
    });

    registerPreviousButton?.addEventListener('click', () => setRegisterStep(currentRegisterStep - 1));
    registerNextButton?.addEventListener('click', () => {
        if (validateStep(currentRegisterStep)) {
            setRegisterStep(currentRegisterStep + 1);
        }
    });

    registerForm.addEventListener('submit', (event) => {
        if (!validateRegistration()) {
            event.preventDefault();
            event.stopPropagation();
        }
    });

    authRoleCards.forEach((card) => {
        card.addEventListener('click', () => setAuthRole(card.dataset.authRole));
    });

    passwordInput?.addEventListener('input', setPasswordConfirmationValidity);
    passwordConfirmationInput?.addEventListener('input', setPasswordConfirmationValidity);
    nameInputs.forEach((input) => {
        input.addEventListener('input', () => sanitizeNameInput(input));
    });
    ageInput?.addEventListener('keydown', (event) => {
        if (['e', 'E', '+', '-', '.', ','].includes(event.key)) {
            event.preventDefault();
        }
    });
    ageInput?.addEventListener('input', sanitizeAgeInput);
    birthdateInput?.addEventListener('change', syncAgeFromBirthdate);
    pwdIdInput?.addEventListener('change', syncPwdIdFileName);

    syncAgeFromBirthdate();
    nameInputs.forEach(sanitizeNameInput);
    sanitizeAgeInput();
    syncPwdIdFileName();
    setAuthRole(authRoleInput?.value);
}
